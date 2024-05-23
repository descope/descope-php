<?php

declare(strict_types=1);

namespace Descope\SDK;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Descope\SDK\Exception\AuthException;
use Descope\SDK\EndpointsV1;

class API
{
    private $httpClient;
    private $projectId;
    private $managementKey;

    /**
     * Constructor for API class.
     *
     * @param string $managementKey Management key for authentication.
     */
    public function __construct(string $projectId, ?string $managementKey)
    {
        $this->httpClient = new Client();
        $this->projectId = $projectId;
        $this->managementKey = $managementKey ?? '';
    }

    /**
     * Requests JwtResponse from Descope APIs with the given body and auth token.
     *
     * @param string $uri URI endpoint.
     * @param array $body Request body.
     * @param bool $useManagementKey Whether to use the management key for authentication.
     * @return array JWT response array.
     * @throws AuthException If the request fails.
     */
    public function doPost(string $uri, array $body, bool $useManagementKey): array
    {
        $authToken = $this->getAuthToken($useManagementKey);
        $jsonBody = json_encode($body);
        try {
            $response = $this->httpClient->post($uri, [
                'headers' => $this->getHeaders($authToken),
                'body' => $jsonBody,
            ]);

            // Ensure the response is an object with getBody method
            if (!is_object($response) || !method_exists($response, 'getBody') || !method_exists($response, 'getHeader')) {
                throw new AuthException(500, 'internal error', 'Invalid response from API');
            }

            return $response;
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            echo "Error: HTTP Status Code: $statusCode, Response: $responseBody";
            return [
                'statusCode' => $statusCode,
                'response' => $responseBody,
            ];
        }
    }

    /**
     * Sends a GET request to the specified URI with an optional auth token.
     *
     * @param string $uri URI endpoint.
     * @param bool $useManagementKey Whether to use the management key for authentication.
     * @return array JWT response array.
     * @throws AuthException If the request fails.
     */
    public function doGet(string $uri, bool $useManagementKey): array
    {
        $authToken = $this->getAuthToken($useManagementKey);
        try {
            $response = $this->httpClient->get($uri, [
                'headers' => $this->getHeaders($authToken),
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            return $this->generateJwtResponse($responseData, $responseData['refreshToken'] ?? null, $responseData['sessionToken'] ?? null);
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            echo "Error: HTTP Status Code: $statusCode, Response: $responseBody";
            return [
                'statusCode' => $statusCode,
                'response' => $responseBody,
            ];
        }
    }

    /**
     * Generates a JWT response array with the given parameters.
     *
     * @param array $resp Response data.
     * @param string|null $refreshToken Refresh token.
     * @param string|null $audience Audience.
     * @return array JWT response array.
     */
    public function generateJwtResponse(array $responseBody, ?string $refreshCookie, ?string $audience): array
    {
        $jwtResponse = $this->generateAuthInfo($responseBody, $refreshCookie, true, $audience);

        $jwtResponse['user'] = $responseBody['user'] ?? [];
        $jwtResponse['firstSeen'] = $responseBody['firstSeen'] ?? true;

        return $jwtResponse;
    }

    /**
     * Generates headers for the HTTP request.
     *
     * @param string|null $authToken Authentication token.
     * @return array Headers array.
     */
    private function getHeaders(string $authToken): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $headers['Authorization'] = "Bearer $authToken";

        return $headers;
    }

    /**
     * Constructs the auth token based on whether the management key is used.
     *
     * @param bool $useManagementKey Whether to use the management key for authentication.
     * @return string The constructed auth token.
     */
    private function getAuthToken(bool $useManagementKey): string
    {
        if ($useManagementKey && !empty($this->managementKey)) {
            return $this->projectId . ':' . $this->managementKey;
        }
        return $this->projectId;
    }

    private function generateAuthInfo(array $responseBody, ?string $refreshToken, bool $userJwt, ?string $audience): array
    {
        $jwtResponse = [];
        $stJwt = $responseBody['sessionJwt'] ?? '';

        if ($stJwt) {
            $jwtResponse[EndpointsV1::SESSION_TOKEN_NAME] = $this->verify($stJwt, $audience);
        }
        
        $rtJwt = $responseBody['refreshJwt'] ?? '';

        if ($refreshToken) {
            $jwtResponse[EndpointsV1::REFRESH_SESSION_TOKEN_NAME] = $this->verify($refreshToken, $audience);
        } elseif ($rtJwt) {
            $jwtResponse[EndpointsV1::REFRESH_SESSION_TOKEN_NAME] = $this->verify($rtJwt, $audience);
        }

        $jwtResponse = $this->adjustProperties($jwtResponse, $userJwt);

        if ($userJwt) {
            $jwtResponse[EndpointsV1::COOKIE_DATA_NAME] = [
                'exp' => $responseBody['cookieExpiration'] ?? 0,
                'maxAge' => $responseBody['cookieMaxAge'] ?? 0,
                'domain' => $responseBody['cookieDomain'] ?? '',
                'path' => $responseBody['cookiePath'] ?? '/',
            ];
        }

        return $jwtResponse;
    }
}