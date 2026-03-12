<?php

namespace Descope\SDK\Configuration;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Descope\SDK\EndpointsV1;
use Descope\SDK\EndpointsV2;
use Descope\SDK\API;
use Descope\SDK\Cache\CacheInterface;
use Descope\SDK\Cache\APCuCache;
use Descope\SDK\Cache\InMemoryCache;

final class SDKConfig
{
    public $client;
    public $projectId;
    public $managementKey;
    public $baseUrl;
    private $cache;
    private $jwksCacheTTL;
    private const JWKS_CACHE_KEY_PREFIX = 'descope_jwks:';
    private const DEFAULT_JWKS_TTL = 600; // 10 minutes for faster key rotation discovery

    public function __construct(array $config, ?CacheInterface $cache = null)
    {
        $this->client = new Client();
        $this->projectId = $config['projectId'];
        $this->managementKey = $config['managementKey'] ?? '';
        $this->baseUrl = $config['baseUrl'] ?? null;
        // Normalize JWKS cache TTL: ensure positive integer or fall back to default
        $ttl = $config['jwksCacheTTL'] ?? self::DEFAULT_JWKS_TTL;
        if (!is_int($ttl)) {
            $ttl = is_numeric($ttl) ? (int) $ttl : self::DEFAULT_JWKS_TTL;
        }
        if ($ttl <= 0) {
            $ttl = self::DEFAULT_JWKS_TTL;
        }
        $this->jwksCacheTTL = $ttl;

        if ($cache) {
            $this->cache = $cache;
        } elseif (extension_loaded('apcu') && (php_sapi_name() !== 'cli' || ini_get('apc.enable_cli'))) {
            $this->cache = new APCuCache();
        } else {
            // Fallback to in-memory cache instead of NullCache
            $this->cache = new InMemoryCache();

            // Only log in debug mode to avoid noise in CLI/cron/test runs
            if (isset($_ENV['DESCOPE_DEBUG']) && $_ENV['DESCOPE_DEBUG'] === 'true') {
                error_log('[Descope SDK] APCu extension not available or not enabled. Using in-memory cache fallback. ' .
                          'For better performance in production, enable APCu extension.');
            }
        }
    }

    /**
     * Gets the current JWKSet. Fetches a new one if not cached or if explicitly requested.
     */
    public function getJWKSets(bool $forceRefresh = false): array
    {
        if (!$forceRefresh) {
            $cachedJWKSets = $this->cache->get($this->getCacheKey());
            if ($cachedJWKSets) {
                return $cachedJWKSets;
            }
        }

        $jwks = $this->fetchJWKSets();
        $this->cache->set($this->getCacheKey(), $jwks, $this->jwksCacheTTL);
        return $jwks;
    }

    /**
     * Fetch the JWK KeySet from the Descope API.
     */
    private function fetchJWKSets(): array
    {
        try {
            $url = EndpointsV2::getPublicKeyPath() . '/' . $this->projectId;
            $response = $this->client->request('GET', $url, [
                'headers' => $this->getSDKHeaders()
            ]);
            $jwkSets = json_decode($response->getBody(), true);

            if (!isset($jwkSets['keys']) || !is_array($jwkSets['keys'])) {
                throw new \Exception('Invalid JWK response');
            }

            return $jwkSets;
        } catch (RequestException $e) {
            throw new \Exception('Failed to fetch JWK KeySet: ' . $e->getMessage());
        }
    }

    /**
     * Generates SDK identification headers for HTTP requests.
     *
     * @return array Headers array with SDK identification.
     */
    private function getSDKHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'x-descope-sdk-name' => 'php',
            'x-descope-sdk-php-version' => PHP_VERSION,
            'x-descope-sdk-version' => EndpointsV1::SDK_VERSION,
        ];
    }

    /**
     * Returns a project-scoped cache key to prevent cross-project JWKS confusion.
     */
    private function getCacheKey(): string
    {
        return self::JWKS_CACHE_KEY_PREFIX . $this->projectId;
    }
}
