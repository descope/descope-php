<?php

namespace Descope\SDK\Cache;

use Illuminate\Support\Facades\Cache;

class LaravelCache implements CacheInterface
{
    public function get(string $key)
    {
        return Cache::get($key);
    }

    public function set(string $key, $value, int $ttl = 3600): bool
    {
        return Cache::put($key, $value, $ttl / 60);
    }

    public function delete(string $key): bool
    {
        return Cache::forget($key);
    }
}