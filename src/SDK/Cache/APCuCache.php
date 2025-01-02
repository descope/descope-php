<?php

namespace Descope\SDK\Cache;

class APCuCache implements CacheInterface
{
    public function get(string $key)
    {
        return apcu_fetch($key);
    }

    public function set(string $key, $value, int $ttl = 3600): bool
    {
        return apcu_store($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return apcu_delete($key);
    }
}