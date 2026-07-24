<?php

declare(strict_types=1);

namespace Descope\SDK;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Descope\SDK\Configuration\HttpClientConfig;
use Descope\SDK\Exception\AuthException;
use Descope\SDK\Exception\DescopeException;
use Descope\SDK\Exception\RateLimitException;
use Descope\SDK\EndpointsV1;
use Descope\SDK\Token\Verifier;

class API
{
    private const RETRYABLE_STATUS_CODES = [503, 520, 521, 522, 524, 530];

    private $httpClient;
    private $projectId;
    private $managementKey;
    private $baseUrl;
    private $debug;
    private $httpClientConfig;

    /** @var int[] Delays between retries in microseconds: 100ms, 5s, 5s */
    protected $retryDelaysUs = [100000, 5000000, 5000000];

    /**
     * Constructor for API class.
     *
     * @param string                $projectId
     * @param string|null           $managementKey  Management key for authentication.
     * @param bool|null             $debug          Enable verbose logging. If null, checks DESCOPE_DEBUG.
     * @param string|null           $baseUrl         Optional explicit base URL override (cluster/region).
     * @param HttpClientConfig|null $httpClientConfig HTTP timeout configuration.
     * @param ClientInterface|null  $httpClient       Optional custom Guzzle-compatible HTTP client.
     */
    public function __construct(
        string $projectId,
        ?string $managementKey,
        ?bool $debug = null,
        ?string $baseUrl = null,
        ?HttpClientConfig $httpClientConfig = null,
        ?ClientInterface $httpClient = null
    ) {
        if ($httpClient !== null) {
            $this->httpClient = $httpClient;
        } elseif (!empty($_ENV['DESCOPE_LOG_PATH'])) {
            $log = new Logger('descope_guzzle_log');
            $log->pushHandler(new StreamHandler($_ENV['DESCOPE_LOG_PATH'], Logger::DEBUG));
            $stack = HandlerStack::create();
            $stack->push(
                Middleware::log(
                    $log,
                    new MessageFormatter(MessageFormatter::DEBUG)
                )
            );
            $this->httpClient = new Client(['handler' => $stack]);
        } else {
            $this->httpClient = new Client();
        }

        $this->projectId = $projectId;
        $this->managementKey = $managementKey ?? '';
        $this->baseUrl = EndpointsV1::resolveBaseUrl($projectId, $baseUrl);
        $this->httpClientConfig = $httpClientConfig ?? new HttpClientConfig();

        // Set debug flag from parameter, environment variable, or default to false
        if ($debug !== null) {
            $this->debug = $debug;
        } else {
            $this->debug = isset($_ENV['DESCOPE_DEBUG']) && $_ENV['DESCOPE_DEBUG'] === 'true';
        }
    }

    /**
     * Recursively transforms empty arrays to empty objects.
     *
     * This function ensures that empty arrays in the input data are
     * converted to empty objects (stdClass) before being JSON encoded.
     *
     * @param  mixed $data The data to transform, which can be an array or any other type.
     * @return mixed The transformed data with empty arrays replaced by empty objects.
     */
    private function transformEmptyArraysToObjects($data)
    {
        if (is_array($data)) {
            // Check if the array is associative
            $isAssociative = count(array_filter(array_keys($data), 'is_string')) > 0;
    
            // If the array is empty and associative, convert to stdClass object
            if (empty($data) && $isAssociative) {
                return new \stdClass();
            }
    
            foreach ($data as $key => &$value) {
                if (is_array($value)) {
                    // Recursively handle nested arrays
                    $value = $this->transformEmptyArraysToObjects($value);
                }
            }
        }
        return $data;
    }

    /**
     * Requests JwtResponse from Descope APIs with the given body and auth token.
     *
     * @param  string $uri              URI endpoint.
     * @param  array  $body             Request body.
     * @param  bool   $useManagementKey Whether to use the management key for authentication.
     * @return array JWT response array.
     * @throws AuthException|RateLimitException|GuzzleException|\JsonException If the request fails.
     */
    public function doPost(string $uri, array $body, ?bool $useManagementKey = false, ?string $refreshToken = null): array
    {
        $authToken = "";

        if ($refreshToken) {
            $authToken = $this->getAuthToken(false, $refreshToken);
        } else {
            $authToken = $this->getAuthToken($useManagementKey, '');
        }

        $uri = $this->resolveRequestUrl($uri);

        $body = $this->transformEmptyArraysToObjects($body);
        $jsonBody = empty($body) ? '{}' : json_encode($body);
        try {
            $headers = $this->getHeaders($authToken);
            $response = $this->executeWithRetry(
                function (array $requestOptions) use ($uri, $jsonBody, $headers) {
                    return $this->httpClient->post(
                        $uri,
                        array_merge($requestOptions, ['headers' => $headers, 'body' => $jsonBody])
                    );
                },
                (bool) $useManagementKey
            );

            // Ensure the response is an object with getBody method
            if (!is_object($response) || !method_exists($response, 'getBody') || !method_exists($response, 'getHeader')) {
                throw new AuthException(500, 'internal error', 'Invalid response from API');
            }

            // Read Body
            $body = $response->getBody();
            $body->rewind();
            $contents = $body->getContents() ?? [];

            return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (RequestException $e) {
            if ($this->debug) {
                $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
                $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
                error_log("Descope SDK [POST] RequestException: " . $e->getMessage());
                error_log("Descope SDK [POST] Error: HTTP Status Code: $statusCode, Response: $responseBody");
            }
            throw $this->createExceptionFromRequestException($e);
        }
    }

    /**
     * Sends a PATCH request to the specified URI with a JSON body and an optional auth token.
     *
     * @param  string $uri              URI endpoint.
     * @param  array  $body             Request body.
     * @param  bool   $useManagementKey Whether to use the management key for authentication.
     * @return array JWT response array.
     * @throws AuthException|RateLimitException|GuzzleException|\JsonException If the request fails.
     */
    public function doPatch(string $uri, array $body, ?bool $useManagementKey = false, ?string $refreshToken = null): array
    {
        $authToken = "";

        if ($refreshToken) {
            $authToken = $this->getAuthToken(false, $refreshToken);
        } else {
            $authToken = $this->getAuthToken($useManagementKey, '');
        }

        $uri = $this->resolveRequestUrl($uri);

        $body = $this->transformEmptyArraysToObjects($body);
        $jsonBody = empty($body) ? '{}' : json_encode($body);
        try {
            $headers = $this->getHeaders($authToken);
            $response = $this->executeWithRetry(
                function (array $requestOptions) use ($uri, $jsonBody, $headers) {
                    return $this->httpClient->patch(
                        $uri,
                        array_merge($requestOptions, ['headers' => $headers, 'body' => $jsonBody])
                    );
                },
                (bool) $useManagementKey
            );

            // Ensure the response is an object with getBody method
            if (!is_object($response) || !method_exists($response, 'getBody') || !method_exists($response, 'getHeader')) {
                throw new AuthException(500, 'internal error', 'Invalid response from API');
            }

            // Read Body
            $body = $response->getBody();
            $body->rewind();
            $contents = $body->getContents() ?? [];

            return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (RequestException $e) {
            if ($this->debug) {
                $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
                $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
                error_log("Descope SDK [PATCH] RequestException: " . $e->getMessage());
                error_log("Descope SDK [PATCH] Error: HTTP Status Code: $statusCode, Response: $responseBody");
            }
            throw $this->createExceptionFromRequestException($e);
        }
    }

    /**
     * Sends a GET request to the specified URI with an optional auth token.
     *
     * @param  string $uri              URI endpoint.
     * @param  bool   $useManagementKey Whether to use the management key for authentication.
     * @return array JWT response array.
     * @throws AuthException|RateLimitException|GuzzleException|\JsonException If the request fails.
     */
    public function doGet(string $uri, bool $useManagementKey, ?string $refreshToken = null): array
    {
        $authToken = "";

        if ($refreshToken) {
            $authToken = $this->getAuthToken(false, $refreshToken);
        } else {
            $authToken = $this->getAuthToken($useManagementKey);
        }

        $uri = $this->resolveRequestUrl($uri);

        try {
            $headers = $this->getHeaders($authToken);
            $response = $this->executeWithRetry(
                function (array $requestOptions) use ($uri, $headers) {
                    return $this->httpClient->get($uri, array_merge($requestOptions, ['headers' => $headers]));
                },
                $useManagementKey
            );

            // Ensure the response is an object with getBody method
            if (!is_object($response) || !method_exists($response, 'getBody') || !method_exists($response, 'getHeader')) {
                throw new AuthException(500, 'internal error', 'Invalid response from API');
            }

            // Read Body
            $body = $response->getBody();
            $body->rewind();
            $contents = $body->getContents() ?? [];

            return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (RequestException $e) {
            if ($this->debug) {
                $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
                $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
                error_log("Descope SDK [GET] Error: HTTP Status Code: $statusCode, Response: $responseBody");
            }
            throw $this->createExceptionFromRequestException($e);
        }
    }

    /**
     * Sends a DELETE request to the specified URI with an auth token.
     *
     * @param  string $uri URI endpoint.
     * @return array JWT response array.
     * @throws AuthException|RateLimitException|GuzzleException|\JsonException If the request fails.
     */
    public function doDelete(string $uri): array
    {
        $authToken = $this->getAuthToken(true);

        $uri = $this->resolveRequestUrl($uri);

        try {
            $headers = $this->getHeaders($authToken);
            $response = $this->executeWithRetry(
                function (array $requestOptions) use ($uri, $headers) {
                    return $this->httpClient->delete($uri, array_merge($requestOptions, ['headers' => $headers]));
                },
                true
            );

            // Ensure the response is an object with getBody method
            if (!is_object($response) || !method_exists($response, 'getBody') || !method_exists($response, 'getHeader')) {
                throw new AuthException(500, 'internal error', 'Invalid response from API');
            }

            // Read Body
            $body = $response->getBody();
            $body->rewind();
            $contents = $body->getContents() ?? [];

            return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (RequestException $e) {
            if ($this->debug) {
                $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
                $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
                error_log("Descope SDK [DELETE] Error: HTTP Status Code: $statusCode, Response: $responseBody");
            }
            throw $this->createExceptionFromRequestException($e);
        }
    }

    /**
     * Generates a JWT response array with the given parameters.
     *
     * @param  array       $responseBody
     * @param  string|null $refreshToken Refresh token.
     * @param  string|null $audience     Audience.
     * @return array JWT response array.
     */
    public function generateJwtResponse(array $responseBody, ?string $refreshToken = null, ?string $audience = null): array
    {
        $jwtResponse = $this->generateAuthInfo($responseBody, $refreshToken, true, $audience);

        $jwtResponse['user'] = $responseBody['user'] ?? [];
        $jwtResponse['firstSeen'] = $responseBody['firstSeen'] ?? true;

        return $jwtResponse;
    }

    /**
     * Executes an HTTP request callable, retrying on transient status codes
     * (503, 520, 521, 522, 524, 530) with delays of 100ms, 5s, 5s.
     * Non-retryable RequestExceptions are re-thrown immediately.
     *
     * The configured request timeout is an end-to-end deadline which includes
     * the time spent waiting between attempts. Each attempt receives the
     * remaining budget as its Guzzle timeout.
     *
     * @param  callable $requestFn        Callable that receives Guzzle request options.
     * @param  bool     $managementRequest Whether to use the management request timeout.
     * @return mixed Guzzle response on success.
     * @throws RequestException On non-retryable errors or after all retries are exhausted.
     */
    private function executeWithRetry(callable $requestFn, bool $managementRequest = false)
    {
        $deadline = $this->currentTime() + $this->httpClientConfig->requestTimeout($managementRequest);
        $lastException = null;

        foreach ($this->retryDelaysUs as $delay) {
            $remainingTime = $deadline - $this->currentTime();
            if ($remainingTime <= 0 && $lastException !== null) {
                throw $lastException;
            }

            try {
                return $requestFn($this->httpClientConfig->requestOptions($remainingTime));
            } catch (RequestException $e) {
                $response = $e->getResponse();
                $statusCode = $response ? $response->getStatusCode() : 0;
                if (!in_array($statusCode, self::RETRYABLE_STATUS_CODES, true)) {
                    throw $e;
                }
                $lastException = $e;

                if (($delay / 1000000) >= ($deadline - $this->currentTime())) {
                    throw $e;
                }
                usleep($delay);
            }
        }

        $remainingTime = $deadline - $this->currentTime();
        if ($remainingTime <= 0 && $lastException !== null) {
            throw $lastException;
        }

        return $requestFn($this->httpClientConfig->requestOptions($remainingTime));
    }

    private function currentTime(): float
    {
        return hrtime(true) / 1000000000;
    }

    /**
     * Builds an AuthException or RateLimitException from a Guzzle RequestException.
     * Parses the response body for Descope error fields when present.
     *
     * @throws AuthException|RateLimitException
     */
    private function createExceptionFromRequestException(RequestException $e): DescopeException
    {
        $response = $e->getResponse();
        $statusCode = $response ? $response->getStatusCode() : 0;
        $responseBody = $response ? $response->getBody()->getContents() : '';

        $errorType = 'RequestException';
        $errorMessage = $e->getMessage();

        if ($responseBody !== '') {
            $decoded = json_decode($responseBody, true);
            if (is_array($decoded)) {
                $errorType = $decoded['errorCode'] ?? $decoded['error'] ?? $errorType;
                $errorMessage = $decoded['errorDescription'] ?? $decoded['errorMessage'] ?? $decoded['message'] ?? $errorMessage;
            }
        }

        if ($statusCode === 429) {
            return new RateLimitException(
                $statusCode,
                $errorType,
                $errorMessage,
                $errorMessage,
                [],
                [],
                $e
            );
        }

        return new AuthException($statusCode, $errorType, $errorMessage, [], $e);
    }

    /**
     * Converts static endpoint values into an instance-bound request URL.
     *
     * Legacy endpoint classes expose full URLs and are mutable process-wide state.
     * Requests may use only their API route and query; the origin always comes from
     * this API instance.
     *
     * @throws AuthException If the value does not contain an SDK API route.
     */
    private function resolveRequestUrl(string $uri): string
    {
        $parts = parse_url($uri);
        $path = is_array($parts) ? ($parts['path'] ?? '') : '';

        if ($path === '') {
            throw new AuthException(400, 'ERROR_TYPE_INVALID_ARGUMENT', 'Invalid SDK API route');
        }

        $routeStart = strpos($path, '/v1/');
        if ($routeStart === false) {
            $routeStart = strpos($path, '/v2/');
        }

        if ($routeStart === false) {
            throw new AuthException(400, 'ERROR_TYPE_INVALID_ARGUMENT', 'Invalid SDK API route');
        }

        $route = substr($path, $routeStart);
        if (strpos($route, '/../') !== false || substr($route, -3) === '/..') {
            throw new AuthException(400, 'ERROR_TYPE_INVALID_ARGUMENT', 'Invalid SDK API route');
        }

        $query = is_array($parts) && isset($parts['query']) ? '?' . $parts['query'] : '';
        return rtrim($this->baseUrl, '/') . $route . $query;
    }

    /**
     * Generates headers for the HTTP request.
     *
     * @param  string|null $authToken Authentication token.
     * @return array Headers array.
     */
    private function getHeaders(string $authToken): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'x-descope-sdk-name' => 'php',
            'x-descope-sdk-php-version' => PHP_VERSION,
            'x-descope-sdk-version' => EndpointsV1::SDK_VERSION,
        ];

        $headers['Authorization'] = "Bearer $authToken";

        return $headers;
    }

    /**
     * Constructs the auth token based on whether the management key is used.
     *
     * @param  bool $useManagementKey Whether to use the management key for authentication.
     * @return string The constructed auth token.
     */
    private function getAuthToken(bool $useManagementKey, ?string $refreshToken = null): string
    {
        if ($useManagementKey && !empty($this->managementKey)) {
            return $this->projectId . ':' . $this->managementKey;
        }

        if ($refreshToken) {
            return $this->projectId . ':' . $refreshToken;
        }

        return $this->projectId;
    }

    /**
     * Generates authentication information from the response body.
     *
     * This method processes the response body to extract JWTs, session data,
     * and cookie settings, and adjusts properties based on the token type.
     *
     * @param  array       $responseBody The API response body containing JWTs and user data.
     * @param  string|null $refreshToken Optional refresh token.
     * @param  bool        $userJwt      Indicates if user-related JWT information should be processed.
     * @param  string|null $audience     Optional audience identifier.
     * @return array The structured JWT response array containing session and user data.
     */
    private function generateAuthInfo(array $responseBody, ?string $refreshToken, bool $userJwt, ?string $audience): array
    {
        $jwtResponse = [];
        $stJwt = $responseBody['sessionJwt'] ?? '';

        if ($stJwt) {
            $jwtResponse[EndpointsV1::$SESSION_TOKEN_NAME] = $stJwt;
        }
        
        $rtJwt = $responseBody['refreshJwt'] ?? '';

        if ($refreshToken) {
            $jwtResponse[EndpointsV1::$REFRESH_TOKEN_NAME] = $refreshToken;
        } elseif ($rtJwt) {
            $jwtResponse[EndpointsV1::$REFRESH_TOKEN_NAME] = $rtJwt;
        }

        $jwtResponse = $this->adjustProperties($jwtResponse, $userJwt);

        if ($userJwt) {
            $jwtResponse[EndpointsV1::$COOKIE_DATA_NAME] = [
                'exp' => $responseBody['cookieExpiration'] ?? 0,
                'maxAge' => $responseBody['cookieMaxAge'] ?? 0,
                'domain' => $responseBody['cookieDomain'] ?? '',
                'path' => $responseBody['cookiePath'] ?? '/',
            ];
        }

        return $jwtResponse;
    }

    /**
     * Adjusts properties of the JWT response array.
     *
     * This method sets permissions, roles, and tenant data from the JWT
     * and processes the issuer and subject values to extract project and user IDs.
     *
     * @param  array $jwtResponse The JWT response array to adjust.
     * @param  bool  $userJwt     Indicates if user-related JWT information should be processed.
     * @return array The adjusted JWT response array with updated properties.
     */
    private function adjustProperties(array $jwtResponse, bool $userJwt): array
    {
        if (isset($jwtResponse[EndpointsV1::$SESSION_TOKEN_NAME])) {
            $jwtResponse['permissions'] = $jwtResponse[EndpointsV1::$SESSION_TOKEN_NAME]['permissions'] ?? [];
            $jwtResponse['roles'] = $jwtResponse[EndpointsV1::$SESSION_TOKEN_NAME]['roles'] ?? [];
            $jwtResponse['tenants'] = $jwtResponse[EndpointsV1::$SESSION_TOKEN_NAME]['tenants'] ?? [];
        } elseif (isset($jwtResponse[EndpointsV1::$REFRESH_TOKEN_NAME])) {
            $jwtResponse['permissions'] = $jwtResponse[EndpointsV1::$REFRESH_TOKEN_NAME]['permissions'] ?? [];
            $jwtResponse['roles'] = $jwtResponse[EndpointsV1::$REFRESH_TOKEN_NAME]['roles'] ?? [];
            $jwtResponse['tenants'] = $jwtResponse[EndpointsV1::$REFRESH_TOKEN_NAME]['tenants'] ?? [];
        } else {
            $jwtResponse['permissions'] = $jwtResponse['permissions'] ?? [];
            $jwtResponse['roles'] = $jwtResponse['roles'] ?? [];
            $jwtResponse['tenants'] = $jwtResponse['tenants'] ?? [];
        }

        $issuer = $jwtResponse[EndpointsV1::$SESSION_TOKEN_NAME]['iss'] ??
                  $jwtResponse[EndpointsV1::$REFRESH_TOKEN_NAME]['iss'] ??
                  $jwtResponse['iss'] ?? '';

        $issuerParts = explode("/", $issuer);
        $jwtResponse['projectId'] = end($issuerParts);

        $sub = $jwtResponse[EndpointsV1::$SESSION_TOKEN_NAME]['sub'] ??
               $jwtResponse[EndpointsV1::$REFRESH_TOKEN_NAME]['sub'] ??
               $jwtResponse['sub'] ?? '';

        if ($userJwt) {
            $jwtResponse['userId'] = $sub;
        } else {
            $jwtResponse['keyId'] = $sub;
        }

        return $jwtResponse;
    }
}
