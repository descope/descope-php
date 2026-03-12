<?php

namespace Descope\SDK\Cache;

/**
 * In-memory cache implementation with TTL support.
 *
 * This cache stores data in a static array with expiration timestamps.
 * It automatically cleans up expired entries and has a maximum size limit
 * to prevent unbounded memory growth.
 */
class InMemoryCache implements CacheInterface
{
    /**
     * @var array<string, array{value: mixed, expires_at: int}> Cache storage
     */
    private static $cache = [];

    /**
     * @var int Maximum number of cache entries to prevent memory issues
     */
    private const MAX_CACHE_SIZE = 100;

    /**
     * @var int Cleanup threshold - trigger cleanup after this many operations
     */
    private const CLEANUP_THRESHOLD = 10;

    /**
     * @var int Counter for operations since last cleanup
     */
    private static $operationCount = 0;

    /**
     * Get a value from cache.
     *
     * @param string $key Cache key
     * @return mixed|null The cached value, or null if not found or expired
     */
    public function get(string $key)
    {
        $this->maybeCleanup();

        if (!isset(self::$cache[$key])) {
            return null;
        }

        $entry = self::$cache[$key];

        // Check if expired
        if (time() >= $entry['expires_at']) {
            unset(self::$cache[$key]);
            return null;
        }

        return $entry['value'];
    }

    /**
     * Set a value in cache with TTL.
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds (default: 3600 = 1 hour)
     * @return bool True on success
     */
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        $this->maybeCleanup();

        // Enforce max cache size by removing oldest entry if needed
        if (count(self::$cache) >= self::MAX_CACHE_SIZE && !isset(self::$cache[$key])) {
            $this->evictOldest();
        }

        self::$cache[$key] = [
            'value' => $value,
            'expires_at' => time() + $ttl
        ];

        return true;
    }

    /**
     * Delete a value from cache.
     *
     * @param string $key Cache key
     * @return bool True on success
     */
    public function delete(string $key): bool
    {
        unset(self::$cache[$key]);
        return true;
    }

    /**
     * Periodically cleanup expired entries to prevent memory bloat.
     */
    private function maybeCleanup(): void
    {
        self::$operationCount++;

        if (self::$operationCount >= self::CLEANUP_THRESHOLD) {
            $this->cleanup();
            self::$operationCount = 0;
        }
    }

    /**
     * Remove all expired entries from cache.
     */
    private function cleanup(): void
    {
        $now = time();
        foreach (self::$cache as $key => $entry) {
            if ($now >= $entry['expires_at']) {
                unset(self::$cache[$key]);
            }
        }
    }

    /**
     * Evict the oldest entry when cache is full.
     * Uses the entry with the earliest expiration time.
     */
    private function evictOldest(): void
    {
        if (empty(self::$cache)) {
            return;
        }

        $oldestKey = null;
        $oldestExpiration = PHP_INT_MAX;

        foreach (self::$cache as $key => $entry) {
            if ($entry['expires_at'] < $oldestExpiration) {
                $oldestExpiration = $entry['expires_at'];
                $oldestKey = $key;
            }
        }

        if ($oldestKey !== null) {
            unset(self::$cache[$oldestKey]);
        }
    }

    /**
     * Clear all cache entries (useful for testing).
     */
    public static function clear(): void
    {
        self::$cache = [];
        self::$operationCount = 0;
    }
}
