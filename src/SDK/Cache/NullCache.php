<?php

namespace Descope\SDK\Cache;

class NullCache implements CacheInterface
{
    public function get(string $key)
    {
        return null;
    }

    public function set(string $key, $value, int $ttl = 3600): bool
    {
        return true;
    }

    public function delete(string $key): bool
    {
        return true;
    }
}