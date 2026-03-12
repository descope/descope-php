<?php

namespace Descope\Tests;

use PHPUnit\Framework\TestCase;
use Descope\SDK\Cache\InMemoryCache;

final class InMemoryCacheTest extends TestCase
{
    private $cache;

    protected function setUp(): void
    {
        // Clear cache before each test
        InMemoryCache::clear();
        $this->cache = new InMemoryCache();
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        InMemoryCache::clear();
    }

    public function testSetAndGet()
    {
        $key = 'test_key';
        $value = ['foo' => 'bar', 'baz' => 123];

        $result = $this->cache->set($key, $value, 60);
        $this->assertTrue($result);

        $retrieved = $this->cache->get($key);
        $this->assertEquals($value, $retrieved);
    }

    public function testGetNonExistentKey()
    {
        $retrieved = $this->cache->get('non_existent_key');
        $this->assertNull($retrieved);
    }

    public function testDelete()
    {
        $key = 'test_key';
        $value = 'test_value';

        $this->cache->set($key, $value, 60);
        $this->assertEquals($value, $this->cache->get($key));

        $result = $this->cache->delete($key);
        $this->assertTrue($result);

        $retrieved = $this->cache->get($key);
        $this->assertNull($retrieved);
    }

    public function testExpiration()
    {
        $key = 'expiring_key';
        $value = 'expiring_value';

        // Set with 1 second TTL
        $this->cache->set($key, $value, 1);

        // Should be available immediately
        $this->assertEquals($value, $this->cache->get($key));

        // Wait for expiration
        sleep(2);

        // Should be expired and return null
        $retrieved = $this->cache->get($key);
        $this->assertNull($retrieved);
    }

    public function testOverwriteExistingKey()
    {
        $key = 'test_key';
        $value1 = 'first_value';
        $value2 = 'second_value';

        $this->cache->set($key, $value1, 60);
        $this->assertEquals($value1, $this->cache->get($key));

        $this->cache->set($key, $value2, 60);
        $this->assertEquals($value2, $this->cache->get($key));
    }

    public function testDefaultTTL()
    {
        $key = 'test_key';
        $value = 'test_value';

        // Set without specifying TTL (should use default of 3600 seconds)
        $this->cache->set($key, $value);

        // Should be available
        $retrieved = $this->cache->get($key);
        $this->assertEquals($value, $retrieved);
    }

    public function testMultipleKeys()
    {
        $data = [
            'key1' => 'value1',
            'key2' => ['complex' => 'array'],
            'key3' => 12345,
        ];

        foreach ($data as $key => $value) {
            $this->cache->set($key, $value, 60);
        }

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $this->cache->get($key));
        }
    }

    public function testCacheSizeLimit()
    {
        // This test verifies that the cache doesn't grow unbounded
        // The max size is 100 entries

        // Fill cache with 100 entries
        for ($i = 0; $i < 100; $i++) {
            $this->cache->set("key_$i", "value_$i", 60);
        }

        // All 100 entries should be retrievable
        for ($i = 0; $i < 100; $i++) {
            $this->assertNotNull($this->cache->get("key_$i"));
        }

        // Adding 101st entry should trigger eviction
        $this->cache->set('key_101', 'value_101', 60);

        // The 101st entry should be cached
        $this->assertEquals('value_101', $this->cache->get('key_101'));

        // At least one of the earlier entries should have been evicted
        // (We can't deterministically test which one without knowing internals)
        $nullCount = 0;
        for ($i = 0; $i < 100; $i++) {
            if ($this->cache->get("key_$i") === null) {
                $nullCount++;
            }
        }
        $this->assertGreaterThan(0, $nullCount);
    }

    public function testClearMethod()
    {
        $this->cache->set('key1', 'value1', 60);
        $this->cache->set('key2', 'value2', 60);

        $this->assertNotNull($this->cache->get('key1'));
        $this->assertNotNull($this->cache->get('key2'));

        InMemoryCache::clear();

        $this->assertNull($this->cache->get('key1'));
        $this->assertNull($this->cache->get('key2'));
    }

    public function testCacheIsStaticAcrossInstances()
    {
        $cache1 = new InMemoryCache();
        $cache2 = new InMemoryCache();

        $cache1->set('shared_key', 'shared_value', 60);

        // Cache should be shared across instances
        $this->assertEquals('shared_value', $cache2->get('shared_key'));
    }

    public function testComplexDataTypes()
    {
        $testData = [
            'array' => ['nested' => ['data' => 'value']],
            'object' => (object) ['prop' => 'value'],
            'number' => 42,
            'float' => 3.14159,
            'boolean' => true,
            'null' => null,
        ];

        $this->cache->set('complex', $testData, 60);
        $retrieved = $this->cache->get('complex');

        $this->assertEquals($testData, $retrieved);
    }
}
