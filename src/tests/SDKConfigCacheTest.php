<?php

namespace Descope\Tests;

use PHPUnit\Framework\TestCase;
use Descope\SDK\Configuration\SDKConfig;
use Descope\SDK\Cache\CacheInterface;
use Descope\SDK\Cache\InMemoryCache;

final class SDKConfigCacheTest extends TestCase
{
    private $config;

    protected function setUp(): void
    {
        $this->config = [
            'projectId' => 'test_project_id',
            'managementKey' => 'test_management_key'
        ];
    }

    protected function tearDown(): void
    {
        InMemoryCache::clear();
    }

    public function testDefaultCacheFallsBackToInMemory()
    {
        // Without APCu (most CI environments), should use InMemoryCache.
        // Verify by using reflection to inspect the private $cache field.
        $sdkConfig = new SDKConfig($this->config);

        $ref = new \ReflectionProperty(SDKConfig::class, 'cache');
        $ref->setAccessible(true);
        $cache = $ref->getValue($sdkConfig);

        $this->assertInstanceOf(InMemoryCache::class, $cache);
    }

    public function testCustomCacheIsUsed()
    {
        $mockCache = $this->createMock(CacheInterface::class);
        $mockCache->method('get')->willReturn(null);
        $mockCache->method('set')->willReturn(true);

        $sdkConfig = new SDKConfig($this->config, $mockCache);

        $ref = new \ReflectionProperty(SDKConfig::class, 'cache');
        $ref->setAccessible(true);

        $this->assertSame($mockCache, $ref->getValue($sdkConfig));
    }

    public function testCustomJWKSCacheTTLIsStored()
    {
        $customConfig = array_merge($this->config, [
            'jwksCacheTTL' => 300
        ]);

        $sdkConfig = new SDKConfig($customConfig);

        $ref = new \ReflectionProperty(SDKConfig::class, 'jwksCacheTTL');
        $ref->setAccessible(true);

        $this->assertSame(300, $ref->getValue($sdkConfig));
    }

    public function testDefaultJWKSCacheTTLIs600()
    {
        $sdkConfig = new SDKConfig($this->config);

        $ref = new \ReflectionProperty(SDKConfig::class, 'jwksCacheTTL');
        $ref->setAccessible(true);

        $this->assertSame(600, $ref->getValue($sdkConfig));
    }

    public function testInvalidTTLFallsBackToDefault()
    {
        // Negative
        $sdkConfig = new SDKConfig(array_merge($this->config, ['jwksCacheTTL' => -10]));
        $ref = new \ReflectionProperty(SDKConfig::class, 'jwksCacheTTL');
        $ref->setAccessible(true);
        $this->assertSame(600, $ref->getValue($sdkConfig));

        // Zero
        $sdkConfig = new SDKConfig(array_merge($this->config, ['jwksCacheTTL' => 0]));
        $this->assertSame(600, $ref->getValue($sdkConfig));

        // Non-numeric string
        $sdkConfig = new SDKConfig(array_merge($this->config, ['jwksCacheTTL' => 'abc']));
        $this->assertSame(600, $ref->getValue($sdkConfig));

        // Numeric string is accepted
        $sdkConfig = new SDKConfig(array_merge($this->config, ['jwksCacheTTL' => '120']));
        $this->assertSame(120, $ref->getValue($sdkConfig));
    }

    public function testGetJWKSetsReturnsCachedData()
    {
        $jwksData = [
            'keys' => [
                [
                    'kty' => 'RSA',
                    'kid' => 'test-key-id',
                    'use' => 'sig',
                    'n' => 'test-modulus',
                    'e' => 'AQAB'
                ]
            ]
        ];

        $mockCache = $this->createMock(CacheInterface::class);

        // Expect get() to be called with project-scoped key
        $mockCache->expects($this->once())
            ->method('get')
            ->with('descope_jwks:test_project_id')
            ->willReturn($jwksData);

        $sdkConfig = new SDKConfig($this->config, $mockCache);
        $result = $sdkConfig->getJWKSets();

        $this->assertSame($jwksData, $result);
    }

    public function testForceRefreshSkipsCacheGet()
    {
        $mockCache = $this->createMock(CacheInterface::class);

        // When forceRefresh is true, get() should NEVER be called
        $mockCache->expects($this->never())
            ->method('get');

        // Allow set() to be called when fresh JWKS is stored
        $mockCache->expects($this->any())
            ->method('set');

        $sdkConfig = new SDKConfig($this->config, $mockCache);

        try {
            $sdkConfig->getJWKSets(true);
        } catch (\Exception $e) {
            // Expected: no real API in test. The assertion is that get() was never called.
            $this->assertStringContainsString('Failed to fetch', $e->getMessage());
        }
    }

    public function testCacheKeyIsScopedByProjectId()
    {
        $cacheA = $this->createMock(CacheInterface::class);
        $cacheB = $this->createMock(CacheInterface::class);

        $jwksA = ['keys' => [['kid' => 'key-a']]];
        $jwksB = ['keys' => [['kid' => 'key-b']]];

        // Project A uses key scoped to projectA
        $cacheA->expects($this->once())
            ->method('get')
            ->with('descope_jwks:projectA')
            ->willReturn($jwksA);

        // Project B uses key scoped to projectB
        $cacheB->expects($this->once())
            ->method('get')
            ->with('descope_jwks:projectB')
            ->willReturn($jwksB);

        $configA = new SDKConfig(['projectId' => 'projectA', 'managementKey' => ''], $cacheA);
        $configB = new SDKConfig(['projectId' => 'projectB', 'managementKey' => ''], $cacheB);

        $this->assertSame($jwksA, $configA->getJWKSets());
        $this->assertSame($jwksB, $configB->getJWKSets());
    }
}
