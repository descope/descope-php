<?php

declare(strict_types=1);

namespace Descope\SDK;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Descope\Exception\AuthException;
use Descope\Common\EndpointsV1;

class API
{
    private $httpClient;
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
        $managementKey ? $this->managementKey = $managementKey : $this->managementKey = '';
    }

    /**
     * Requests JwtResponse from Descope APIs with the given body and auth token.
     *
     * @param string $uri URI endpoint.
     * @param array $body Request body.
     * @param string|null $authToken Authentication token.
     * @return \Psr\Http\Message\ResponseInterface Response from the server.
     * @throws AuthException If the request fails.
     */
    public function doPost(string $uri, array $body, boolean $useManagementKey): \Psr\Http\Message\ResponseInterface
    {
        $authToken = $useManagementKey ? ($this->config->getProjectId():$this->config->getManagementKey()) : $this->config->getProjectId();
        try {
            $response = $this->httpClient->post($uri, [
                'headers' => $this->getHeaders($authToken),
                'json' => $body,
            ]);
            return $response;
        } catch (RequestException $e) {
            throw new AuthException($e->getResponse()->getStatusCode(), 'request error', $e->getMessage());
        }
    }

    /**
     * Sends a GET request to the specified URI with an optional auth token.
     *
     * @param string $uri URI endpoint.
     * @param string|null $authToken Authentication token.
     * @return \Psr\Http\Message\ResponseInterface Response from the server.
     * @throws AuthException If the request fails.
     */
    public function doGet(string $uri, boolean $useManagementKey): \Psr\Http\Message\ResponseInterface
    {
        $authToken = $useManagementKey ? ($this->config->getProjectId():$this->config->getManagementKey()) : $this->config->getProjectId();

        try {
            $response = $this->httpClient->get($uri, [
                'headers' => $this->getHeaders($authToken),
            ]);
            return $response;
        } catch (RequestException $e) {
            throw new AuthException($e->getResponse()->getStatusCode(), 'request error', $e->getMessage());
        }
    }

    /**
     * Generates a JWT response array with the given parameters.
     *
     * @param array $resp Response data.
     * @param string|null $refreshToken Refresh token.
     * @param string|null $sessionToken Session token.
     * @return array JWT response array.
     */
    public function generateJwtResponse(array $resp, ?string $refreshToken, ?string $sessionToken): array
    {
        $jwtResponse = [
            'jwt' => $resp['jwt'] ?? null,
            'refreshToken' => $refreshToken,
            'sessionToken' => $sessionToken,
        ];

        if (isset($resp['user'])) {
            $jwtResponse['user'] = $resp['user'];
        }

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
}