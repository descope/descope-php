<?php

namespace Descope\SDK\Configuration;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Descope\SDK\EndpointsV2;
use Descope\SDK\API;

final class SDKConfig
{
    public $client;
    public $projectId;
    public $managementKey;
    public $jwkSets;
    private $cachedJWKSets = null;

    public function __construct(array $config)
    {
        $this->client = new Client();
        $this->projectId = $config['projectId'];
        $this->managementKey = $config['managementKey'] ?? '';
        
        EndpointsV2::setBaseUrl($config['projectId']);
    }

    /**
     * Gets the current JWK KeySet. Fetches a new one if not cached or if explicitly requested.
     */
    public function getJWKSets(bool $forceRefresh = false): array
    {
        // Return cached JWK if it exists and no refresh is requested
        if ($this->cachedJWKSets !== null && !$forceRefresh) {
            return $this->cachedJWKSets;
        }

        // Fetch new JWK KeySet from Descope API
        $this->cachedJWKSets = $this->fetchJWKSets();
        return $this->cachedJWKSets;
    }

    /**
     * Fetch the JWK KeySet from the Descope API.
     */
    private function fetchJWKSets(): array
    {
        try {
            $url = EndpointsV2::getPublicKeyPath() . '/' . $this->projectId;
<<<<<<< Updated upstream
            $response = $this->client->request('GET', $url);
            $jwkSets = json_decode($response->getBody(), true);

            if (!isset($jwkSets['keys']) || !is_array($jwkSets['keys'])) {
                throw new \Exception('Invalid JWK response');
            }

=======
            
            // Fetch JWK public key from Descope API
            $res = $this->client->request('GET', $url);
            $jwkSets = json_decode($res->getBody(), true);
>>>>>>> Stashed changes
            return $jwkSets;
        } catch (RequestException $e) {
            throw new \Exception('Failed to fetch JWK KeySet: ' . $e->getMessage());
        }
    }
}
