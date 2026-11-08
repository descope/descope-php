<?php

namespace Descope\SDK\Configuration;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Descope\SDK\EndpointsV1;
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
    private const JWKS_CACHE_KEY_PREFIX = 'descope_jwks:v2:';
    private const DEFAULT_JWKS_TTL = 600; // 10 minutes for faster key rotation discovery
    private const JWKS_REFRESH_COOLDOWN = 30; // minimum seconds between key fetches for forced refreshes
    private const JWKS_MAX_AGE = 3600; // cached keys are not used past one hour since the last successful fetch

    public function __construct(
        array $config,
        ?CacheInterface $cache = null,
        ?float $requestTimeout = null,
        ?ClientInterface $httpClient = null
    ) {
        if ($httpClient !== null) {
            // Respect a caller-supplied client and its own transport configuration.
            $this->client = $httpClient;
        } else {
            $this->client = new Client([
                'timeout' => $requestTimeout ?? API::DEFAULT_REQUEST_TIMEOUT_SECONDS,
                'connect_timeout' => API::DEFAULT_CONNECT_TIMEOUT_SECONDS,
            ]);
        }
        $this->projectId = $config['projectId'];
        $this->managementKey = $config['managementKey'] ?? '';
        $this->baseUrl = EndpointsV1::resolveBaseUrl($this->projectId, $config['baseUrl'] ?? null);
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
     * Gets the current JWKSet. The cached set is refreshed after jwksCacheTTL and,
     * if the fetch fails, kept until one hour after the last successful fetch.
     * A forced refresh (unknown kid) fetches unless a fetch succeeded within the
     * cooldown. Once the set is stale, a failed fetch blocks retries for the cooldown.
     */
    public function getJWKSets(bool $forceRefresh = false): array
    {
        $now = time();
        $entry = $this->cache->get($this->getCacheKey());
        // The fetch time is stored next to `keys` so older SDK versions sharing the cache still read the set.
        // Entries without it (written by those versions) are refreshed.
        $cached = isset($entry['fetchedAt'], $entry['keys']) ? array_diff_key($entry, ['fetchedAt' => true]) : null;
        $age = $cached ? $now - $entry['fetchedAt'] : PHP_INT_MAX;
        $usable = $age < self::JWKS_MAX_AGE;

        if ($usable && $age < $this->jwksCacheTTL) {
            if (!$forceRefresh || $this->cache->get($this->getCooldownKey())) {
                return $cached;
            }
        } elseif ($this->cache->get($this->getAttemptKey())) {
            return $usable ? $cached : ['keys' => []];
        }

        try {
            $jwks = $this->fetchJWKSets();
        } catch (\Exception $e) {
            error_log('[Descope SDK] Warning: ' . $e->getMessage());
            $this->cache->set($this->getAttemptKey(), true, self::JWKS_REFRESH_COOLDOWN);
            if ($usable) {
                return $cached;
            }
            throw $e;
        }
        $this->cache->set($this->getCacheKey(), $jwks + ['fetchedAt' => $now], self::JWKS_MAX_AGE);
        $this->cache->set($this->getCooldownKey(), true, self::JWKS_REFRESH_COOLDOWN);
        return $jwks;
    }

    /**
     * Fetch the JWK KeySet from the Descope API.
     */
    private function fetchJWKSets(): array
    {
        try {
            $url = rtrim($this->baseUrl, '/') . '/v2/keys/' . rawurlencode($this->projectId);
            $response = $this->client->request('GET', $url, [
                'headers' => $this->getSDKHeaders()
            ]);
            $jwkSets = json_decode($response->getBody(), true);

            if (empty($jwkSets['keys']) || !is_array($jwkSets['keys'])) {
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
     * Returns a source- and project-scoped cache key to prevent JWKS reuse
     * between SDK instances configured for different API origins.
     */
    private function getCacheKey(): string
    {
        return self::JWKS_CACHE_KEY_PREFIX . hash('sha256', $this->canonicalBaseUrl() . "\0" . $this->projectId);
    }

    private function getCooldownKey(): string
    {
        return $this->getCacheKey() . ':cooldown';
    }

    private function getAttemptKey(): string
    {
        return $this->getCacheKey() . ':attempt';
    }

    private function canonicalBaseUrl(): string
    {
        $parts = parse_url($this->baseUrl);
        if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
            return rtrim($this->baseUrl, '/');
        }

        $scheme = strtolower($parts['scheme']);
        $host = strtolower($parts['host']);
        $port = $parts['port'] ?? null;
        if (($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80)) {
            $port = null;
        }

        $path = isset($parts['path']) ? rtrim($parts['path'], '/') : '';
        return $scheme . '://' . $host . ($port === null ? '' : ':' . $port) . $path;
    }
}
