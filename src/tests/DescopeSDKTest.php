<?php

namespace Descope\Tests;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;
use Descope\SDK\API;
use Descope\SDK\Auth\Password;
use Descope\SDK\Auth\SSO;
use Descope\SDK\Management\Management;
use Descope\SDK\Cache\CacheInterface;
use Descope\SDK\Cache\InMemoryCache;
use Descope\SDK\Configuration\SDKConfig;
use Descope\SDK\Exception\TokenException;
use Descope\SDK\Exception\ValidationException;
use Descope\SDK\EndpointsV1;
use Descope\SDK\EndpointsV2;
use Descope\SDK\Token\Extractor;
use Descope\SDK\Management\MgmtV1;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

final class DescopeSDKTest extends TestCase
{
    private $config;
    private $sdk;

    protected function setUp(): void
    {
        $this->config = [
            'projectId' => 'test_project_id',
            'managementKey' => 'test_management_key'
        ];
        $this->sdk = new DescopeSDK($this->config);
    }

    protected function tearDown(): void
    {
        InMemoryCache::clear();
    }

    public function testConstructorInitializesComponents()
    {
        $this->assertInstanceOf(Password::class, $this->sdk->password());
        $this->assertInstanceOf(SSO::class, $this->sdk->sso());
        $this->assertInstanceOf(Management::class, $this->sdk->management());
    }

    public function testVerifyThrowsExceptionWithoutToken()
    {
        $this->expectException(ValidationException::class);
        $this->sdk->verify(null);
    }

    public function testGetClaimsRejectsForgedTokenClaims()
    {
        $extractor = $this->extractorWithTestKey($this->privateKey());
        $token = $this->jwt(
            ['alg' => 'RS256', 'typ' => 'JWT', 'kid' => 'legit-key'],
            ['iss' => 'test_project_id', 'sub' => 'attacker', 'roles' => ['admin'], 'exp' => time() + 3600],
            'not_a_real_rsa_signature'
        );

        $this->expectException(TokenException::class);
        $extractor->getClaims($token);
    }

    public function testGetClaimsRejectsExpiredTokenClaims()
    {
        $privateKey = $this->privateKey();
        $extractor = $this->extractorWithTestKey($privateKey);
        $token = $this->signedJwt(
            ['alg' => 'RS256', 'typ' => 'JWT', 'kid' => 'legit-key'],
            ['iss' => 'test_project_id', 'sub' => 'user', 'roles' => ['admin'], 'exp' => time() - 1],
            $privateKey
        );

        $this->expectException(TokenException::class);
        $extractor->getClaims($token);
    }

    public function testJWKSFromAnotherOriginCannotVerifyAttackerToken(): void
    {
        $projectId = 'shared-project';
        $cache = new InMemoryCache();
        $attackerPrivateKey = $this->privateKey();
        $trustedPrivateKey = $this->privateKey();

        $attackerConfig = new SDKConfig([
            'projectId' => $projectId,
            'baseUrl' => 'https://attacker.example.com',
        ], $cache);
        $attackerConfig->client = new Client([
            'handler' => HandlerStack::create(new MockHandler([
                new Response(200, [], json_encode(['keys' => [$this->jwkFromPrivateKey($attackerPrivateKey, 'shared-key')]])),
            ])),
        ]);
        $attackerConfig->getJWKSets();

        $trustedRequests = [];
        $trustedConfig = new SDKConfig([
            'projectId' => $projectId,
            'baseUrl' => 'https://api.descope.com',
        ], $cache);
        $trustedStack = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['keys' => [$this->jwkFromPrivateKey($trustedPrivateKey, 'shared-key')]])),
        ]));
        $trustedStack->push(Middleware::history($trustedRequests));
        $trustedConfig->client = new Client(['handler' => $trustedStack]);

        $token = $this->signedJwt(
            ['alg' => 'RS256', 'typ' => 'JWT', 'kid' => 'shared-key'],
            ['iss' => $projectId, 'sub' => 'attacker', 'exp' => time() + 3600],
            $attackerPrivateKey
        );

        try {
            (new Extractor($trustedConfig))->getClaims($token);
            $this->fail('Expected the attacker-signed token to be rejected');
        } catch (TokenException $e) {
            $this->assertStringContainsString('Invalid signature', $e->getMessage());
        }

        $this->assertCount(1, $trustedRequests);
        $this->assertSame('https://api.descope.com/v2/keys/shared-project', (string) $trustedRequests[0]['request']->getUri());
    }

    public function testRefreshSessionThrowsExceptionWithoutToken()
    {
        $this->expectException(ValidationException::class);
        $this->sdk->refreshSession(null);
    }

    public function testConstructorWithBaseUrl()
    {
        $configWithBaseUrl = [
            'projectId' => 'P2OkfVnJi5Ht7mpCqHjx17nV5epH',
            'baseUrl' => 'https://api-custom.descope.com'
        ];
        
        $sdk = new DescopeSDK($configWithBaseUrl);
        
        // Verify that the baseUrl is set correctly
        $this->assertEquals('https://api-custom.descope.com', EndpointsV1::$baseUrl);
        $this->assertStringContainsString('https://api-custom.descope.com', EndpointsV2::getPublicKeyPath());
    }

    public function testConstructorWithBaseUrlAndManagementKey()
    {
        $configWithBaseUrl = [
            'projectId' => 'P2OkfVnJi5Ht7mpCqHjx17nV5epH',
            'managementKey' => 'test_management_key',
            'baseUrl' => 'https://api-eu.descope.com'
        ];
        
        $sdk = new DescopeSDK($configWithBaseUrl);
        
        // Verify that the baseUrl is set correctly for all endpoint classes
        $this->assertEquals('https://api-eu.descope.com', EndpointsV1::$baseUrl);
        $this->assertStringContainsString('https://api-eu.descope.com', EndpointsV2::getPublicKeyPath());
        
        // Verify management component is initialized
        $this->assertInstanceOf(Management::class, $sdk->management());
    }

    public function testConstructorWithoutBaseUrlUsesProjectId()
    {
        $configWithoutBaseUrl = [
            'projectId' => 'Peuc12z2SP0AQgrqkHCdD7u5fRJ4lOta'
        ];
        
        $sdk = new DescopeSDK($configWithoutBaseUrl);
        
        // Verify that the baseUrl is derived from projectId (region extraction)
        $this->assertStringContainsString('api.euc1.descope.com', EndpointsV1::$baseUrl);
        $this->assertStringContainsString('api.euc1.descope.com', EndpointsV2::getPublicKeyPath());
    }

    public function testConstructorWithEmptyBaseUrlUsesProjectId()
    {
        $configWithEmptyBaseUrl = [
            'projectId' => 'Peuc12z2SP0AQgrqkHCdD7u5fRJ4lOta',
            'baseUrl' => ''
        ];
        
        $sdk = new DescopeSDK($configWithEmptyBaseUrl);
        
        // Verify that empty baseUrl falls back to projectId-based URL
        $this->assertStringContainsString('api.euc1.descope.com', EndpointsV1::$baseUrl);
        $this->assertStringContainsString('api.euc1.descope.com', EndpointsV2::getPublicKeyPath());
    }

    public function testNullableParameterTypes()
    {
        // Test that methods accept null parameters without deprecation warnings
        $this->expectException(ValidationException::class);
        $this->sdk->refreshSession(null);
    }

    public function testNullableParameterTypesForMultipleMethods()
    {
        // Test all methods that have nullable parameters
        $methods = [
            'refreshSession' => [null],
            'getUserDetails' => [null],
            'logout' => [null],
            'logoutAll' => [null],
            'verifyAndRefreshSession' => [null, null]
        ];

        foreach ($methods as $method => $args) {
            try {
                call_user_func_array([$this->sdk, $method], $args);
            } catch (\Exception $e) {
                // Expected exception for missing tokens
                $this->assertStringContainsString('cannot be null or empty', $e->getMessage());
            }
        }
    }

    private function jwt(array $header, array $payload, string $signature): string
    {
        return $this->base64UrlEncode(json_encode($header)) . '.' .
            $this->base64UrlEncode(json_encode($payload)) . '.' .
            $this->base64UrlEncode($signature);
    }

    private function signedJwt(array $header, array $payload, $privateKey): string
    {
        $signedData = $this->base64UrlEncode(json_encode($header)) . '.' .
            $this->base64UrlEncode(json_encode($payload));
        openssl_sign($signedData, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        return $signedData . '.' . $this->base64UrlEncode($signature);
    }

    private function extractorWithTestKey($privateKey): Extractor
    {
        $key = $this->jwkFromPrivateKey($privateKey);
        $cache = new class($key) implements CacheInterface {
            private $key;

            public function __construct(array $key)
            {
                $this->key = $key;
            }

            public function get(string $key)
            {
                return ['keys' => [$this->key]];
            }

            public function set(string $key, $value, int $ttl = 3600): bool
            {
                return true;
            }

            public function delete(string $key): bool
            {
                return true;
            }
        };

        return new Extractor(new SDKConfig(['projectId' => 'test_project_id'], $cache));
    }

    private function privateKey()
    {
        return openssl_pkey_new([
            'private_key_bits' => 1024,
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        ]);
    }

    private function jwkFromPrivateKey($privateKey, string $kid = 'legit-key'): array
    {
        $details = openssl_pkey_get_details($privateKey);
        return [
            'kid' => $kid,
            'kty' => 'RSA',
            'n' => $this->base64UrlEncode($details['rsa']['n']),
            'e' => $this->base64UrlEncode($details['rsa']['e'])
        ];
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
