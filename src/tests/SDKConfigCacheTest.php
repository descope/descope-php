<?php

namespace Descope\Tests;

use PHPUnit\Framework\TestCase;
use Descope\SDK\Configuration\SDKConfig;
use Descope\SDK\Cache\CacheInterface;
use Descope\SDK\Cache\InMemoryCache;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

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

    private function cacheKey(string $baseUrl, string $projectId): string
    {
        return 'descope_jwks:v2:' . hash('sha256', $baseUrl . "\0" . $projectId);
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

        // Expect get() to be called with source- and project-scoped key.
        $mockCache->expects($this->once())
            ->method('get')
            ->with($this->cacheKey('https://api.descope.com', 'test_project_id'))
            ->willReturn($jwksData + ['fetchedAt' => time()]);

        $sdkConfig = new SDKConfig($this->config, $mockCache);
        $result = $sdkConfig->getJWKSets();

        $this->assertSame($jwksData, $result);
    }

    public function testForceRefreshWithinCooldownDoesNotFetch()
    {
        [$cache, $config, $requests] = $this->jwksFixture([['a'], ['a', 'b']]);

        $this->assertSame(['a'], $this->kids($config->getJWKSets()));

        // Unknown kid lookups repeat within the cooldown: no network call.
        $cache->now += 29;
        $this->assertSame(['a'], $this->kids($config->getJWKSets(true)));
        $this->assertSame(['a'], $this->kids($config->getJWKSets(true)));
        $this->assertCount(1, $requests);

        // After the cooldown a forced refresh fetches again and replaces the set.
        $cache->now += 2;
        $this->assertSame(['a', 'b'], $this->kids($config->getJWKSets(true)));
        $this->assertCount(2, $requests);
    }

    public function testFailedForceRefreshDoesNotStartCooldown()
    {
        [$cache, $config, $requests] = $this->jwksFixture([['a'], new Response(500), ['a', 'b'], ['a', 'b', 'c']]);

        $config->getJWKSets();
        $cache->now += 31;

        // Failed refresh keeps the cached keys; the next lookup retries right away.
        $this->assertSame(['a'], $this->kids($config->getJWKSets(true)));
        $this->assertSame(['a', 'b'], $this->kids($config->getJWKSets(true)));
        $this->assertCount(3, $requests);

        // The successful refresh starts the cooldown.
        $this->assertSame(['a', 'b'], $this->kids($config->getJWKSets(true)));
        $this->assertCount(3, $requests);
    }

    public function testKeySetIsReplacedAfterTTL()
    {
        [$cache, $config, $requests] = $this->jwksFixture([['a', 'b'], ['b'], ['b']]);

        $this->assertSame(['a', 'b'], $this->kids($config->getJWKSets()));
        $this->age($cache, 599);
        $this->assertSame(['a', 'b'], $this->kids($config->getJWKSets()));
        $this->assertCount(1, $requests);

        // Past the TTL the whole set is refetched and replaced: key "a" is gone.
        $this->age($cache, 2);
        $this->assertSame(['b'], $this->kids($config->getJWKSets()));
        $this->assertCount(2, $requests);
    }

    public function testOutageKeepsCachedKeysUntilOneHour()
    {
        $log = tempnam(sys_get_temp_dir(), 'jwks');
        $prevLog = ini_set('error_log', $log);
        [$cache, $config, $requests] = $this->jwksFixture([['a'], new Response(500), new Response(500), new Response(500)]);

        $config->getJWKSets();
        $this->age($cache, 601);
        $this->assertSame(['a'], $this->kids($config->getJWKSets()));
        $this->assertCount(2, $requests);
        $this->assertStringContainsString('Warning', file_get_contents($log));

        $this->age($cache, 2900);
        $this->assertSame(['a'], $this->kids($config->getJWKSets()));
        $this->assertCount(3, $requests);

        // One hour after the last successful fetch the cached keys are no longer used.
        $this->age($cache, 100);
        $this->assertFetchFails($config);
        $this->assertCount(4, $requests);
        ini_set('error_log', $prevLog === false ? '' : $prevLog);
        unlink($log);
    }

    public function testFailedRefreshOfStaleKeysIsSpacedBy30Seconds()
    {
        [$cache, $config, $requests] = $this->jwksFixture([['a'], new Response(500), ['a', 'b']]);

        $config->getJWKSets();
        $this->age($cache, 601);
        $this->assertSame(['a'], $this->kids($config->getJWKSets()));
        $this->age($cache, 29);
        $this->assertSame(['a'], $this->kids($config->getJWKSets()));
        $this->assertSame(['a'], $this->kids($config->getJWKSets()));
        $this->assertCount(2, $requests);

        $this->age($cache, 2);
        $this->assertSame(['a', 'b'], $this->kids($config->getJWKSets()));
        $this->assertCount(3, $requests);
    }

    public function testFailedFetchPastOneHourIsSpacedBy30Seconds()
    {
        [$cache, $config, $requests] = $this->jwksFixture([['a'], new Response(500), ['b']]);

        $config->getJWKSets();
        $this->age($cache, 3601);
        $this->assertFetchFails($config);
        $this->assertCount(2, $requests);

        // Within 30 seconds: no fetch and no stored keys are used.
        $this->age($cache, 29);
        $this->assertSame([], $this->kids($config->getJWKSets()));
        $this->assertSame([], $this->kids($config->getJWKSets(true)));
        $this->assertCount(2, $requests);

        $this->age($cache, 2);
        $this->assertSame(['b'], $this->kids($config->getJWKSets()));
        $this->assertCount(3, $requests);
    }

    public function testColdStartFailureIsRetriedAfter30Seconds()
    {
        [$cache, $config, $requests] = $this->jwksFixture([new Response(500), ['a']]);

        $this->assertFetchFails($config);
        $this->assertCount(1, $requests);

        $cache->now += 29;
        $this->assertSame([], $this->kids($config->getJWKSets()));
        $this->assertCount(1, $requests);

        $cache->now += 2;
        $this->assertSame(['a'], $this->kids($config->getJWKSets()));
        $this->assertCount(2, $requests);
    }

    public function testEmptyKeySetIsAFailedFetchAndKeepsCachedKeys()
    {
        [$cache, $config, $requests] = $this->jwksFixture([['a'], [], ['b']]);

        $config->getJWKSets();
        $this->age($cache, 601);

        $this->assertSame(['a'], $this->kids($config->getJWKSets()));
        $this->assertCount(2, $requests);

        $this->age($cache, 31);
        $this->assertSame(['b'], $this->kids($config->getJWKSets()));
    }

    public function testCacheEntryWithoutFetchTimeIsRefreshed()
    {
        [$cache, $config, $requests] = $this->jwksFixture([['new']]);

        $cache->set($this->cacheKey('https://api.descope.com', 'test_project_id'), ['keys' => [['kid' => 'old']]], 3600);

        $this->assertSame(['new'], $this->kids($config->getJWKSets()));
        $this->assertCount(1, $requests);
    }

    public function testStoredEntryKeepsKeysAtTopLevelForOlderReaders()
    {
        [$cache, $config] = $this->jwksFixture([['a']]);

        $config->getJWKSets();

        $entry = $cache->get($this->cacheKey('https://api.descope.com', 'test_project_id'));
        $this->assertSame(['a'], $this->kids($entry));
    }

    public function testFailedFetchWithoutCachedKeysThrows()
    {
        [, $config] = $this->jwksFixture([new Response(500)]);

        $this->expectException(\Exception::class);
        $config->getJWKSets();
    }

    public function testCacheKeyIsScopedByProjectId()
    {
        $cacheA = $this->createMock(CacheInterface::class);
        $cacheB = $this->createMock(CacheInterface::class);

        $jwksA = ['keys' => [['kid' => 'key-a']]];
        $jwksB = ['keys' => [['kid' => 'key-b']]];

        // Project A uses a source- and project-scoped key.
        $cacheA->expects($this->once())
            ->method('get')
            ->with($this->cacheKey('https://api.descope.com', 'projectA'))
            ->willReturn($jwksA + ['fetchedAt' => time()]);

        // Project B uses a source- and project-scoped key.
        $cacheB->expects($this->once())
            ->method('get')
            ->with($this->cacheKey('https://api.descope.com', 'projectB'))
            ->willReturn($jwksB + ['fetchedAt' => time()]);

        $configA = new SDKConfig(['projectId' => 'projectA', 'managementKey' => ''], $cacheA);
        $configB = new SDKConfig(['projectId' => 'projectB', 'managementKey' => ''], $cacheB);

        $this->assertSame($jwksA, $configA->getJWKSets());
        $this->assertSame($jwksB, $configB->getJWKSets());
    }

    public function testSameProjectDifferentOriginsDoNotShareJWKS(): void
    {
        $cache = new InMemoryCache();
        $projectId = 'shared-project';

        $attackerConfig = new SDKConfig([
            'projectId' => $projectId,
            'baseUrl' => 'https://attacker.example.com',
        ], $cache);
        $attackerConfig->client = new Client([
            'handler' => HandlerStack::create(new MockHandler([
                new Response(200, [], json_encode(['keys' => [['kid' => 'attacker-key']]])),
            ])),
        ]);
        $attackerConfig->getJWKSets();

        $requests = [];
        $trustedConfig = new SDKConfig([
            'projectId' => $projectId,
            'baseUrl' => 'https://api.descope.com',
        ], $cache);
        $stack = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['keys' => [['kid' => 'trusted-key']]])),
        ]));
        $stack->push(Middleware::history($requests));
        $trustedConfig->client = new Client(['handler' => $stack]);

        $this->assertSame(['keys' => [['kid' => 'trusted-key']]], $trustedConfig->getJWKSets());
        $this->assertCount(1, $requests);
        $this->assertSame('https://api.descope.com/v2/keys/shared-project', (string) $requests[0]['request']->getUri());
    }

    private function jwksFixture(array $responses): array
    {
        $cache = new class implements CacheInterface {
            public $now = 1000;
            private $items = [];

            public function get(string $key)
            {
                return isset($this->items[$key]) && $this->now < $this->items[$key][1] ? $this->items[$key][0] : null;
            }

            public function set(string $key, $value, int $ttl = 3600): bool
            {
                $this->items[$key] = [$value, $this->now + $ttl];
                return true;
            }

            public function delete(string $key): bool
            {
                unset($this->items[$key]);
                return true;
            }

            /** Moves the clock forward and back-dates a stored key set; its expiry is unchanged. */
            public function age(string $key, int $seconds): void
            {
                $this->items[$key][0]['fetchedAt'] -= $seconds;
                $this->now += $seconds;
            }
        };
        $queue = array_map(function ($r) {
            return $r instanceof Response ? $r : new Response(200, [], json_encode([
                'keys' => array_map(function ($kid) {
                    return ['kid' => $kid];
                }, $r),
            ]));
        }, $responses);
        $requests = new \ArrayObject();
        $stack = HandlerStack::create(new MockHandler($queue));
        $stack->push(Middleware::history($requests));
        $config = new SDKConfig($this->config, $cache, null, new Client(['handler' => $stack]));

        return [$cache, $config, $requests];
    }

    private function kids(array $jwks): array
    {
        return array_column($jwks['keys'], 'kid');
    }

    private function age($cache, int $seconds): void
    {
        $cache->age($this->cacheKey('https://api.descope.com', 'test_project_id'), $seconds);
    }

    private function assertFetchFails(SDKConfig $config): void
    {
        $thrown = false;
        try {
            $config->getJWKSets();
        } catch (\Exception $e) {
            $thrown = true;
        }
        $this->assertTrue($thrown, 'expected the fetch error');
    }
}
