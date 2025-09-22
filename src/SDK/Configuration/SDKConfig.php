<?php

namespace Descope\SDK\Configuration;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Descope\SDK\EndpointsV2;
use Descope\SDK\API;
use Descope\SDK\Cache\CacheInterface;
use Descope\SDK\Cache\APCuCache;
use Descope\SDK\Cache\NullCache;

final class SDKConfig
{
    public $client;
    public $projectId;
    public $managementKey;
    public $baseUrl;
    private $cache;
    private const JWKS_CACHE_KEY = 'descope_jwks';

    public function __construct(array $config, ?CacheInterface $cache = null)
    {
        $this->client = new Client();
        $this->projectId = $config['projectId'];
        $this->managementKey = $config['managementKey'] ?? '';
        $this->baseUrl = $config['baseUrl'] ?? null;
        
        if ($cache) {
            $this->cache = $cache;
        } elseif (extension_loaded('apcu') && ini_get('apc.enable_cli')) {
            $this->cache = new APCuCache();
        } else {
            $this->cache = new NullCache();
            error_log('APCu is not enabled. Falling back to NullCache. Caching is disabled.');
        }
        
        // Base URL handling is managed by DescopeSDK constructor; no need to duplicate here.
    }

    /**
     * Gets the current JWKSet. Fetches a new one if not cached or if explicitly requested.
     */
    public function getJWKSets(bool $forceRefresh = false): array
    {
        if (!$forceRefresh) {
            $cachedJWKSets = $this->cache->get(self::JWKS_CACHE_KEY);
            if ($cachedJWKSets) {
                return $cachedJWKSets;
            }
        }

        $jwks = $this->fetchJWKSets();
        $this->cache->set(self::JWKS_CACHE_KEY, $jwks, 3600); // Cache for 1 hour
        return $jwks;
    }

    /**
     * Fetch the JWK KeySet from the Descope API.
     */
    private function fetchJWKSets(): array
    {
        try {
            $url = EndpointsV2::getPublicKeyPath() . '/' . $this->projectId;
            $response = $this->client->request('GET', $url);
            $jwkSets = json_decode($response->getBody(), true);

            if (!isset($jwkSets['keys']) || !is_array($jwkSets['keys'])) {
                throw new \Exception('Invalid JWK response');
            }

            return $jwkSets;
        } catch (RequestException $e) {
            throw new \Exception('Failed to fetch JWK KeySet: ' . $e->getMessage());
        }
    }
}
