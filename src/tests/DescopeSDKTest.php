<?php

namespace Descope\Tests;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;
use Descope\SDK\API;
use Descope\SDK\Auth\Password;
use Descope\SDK\Auth\SSO;
use Descope\SDK\Management\Management;
use Descope\SDK\Cache\CacheInterface;
use Descope\SDK\Configuration\SDKConfig;
use Descope\SDK\Exception\TokenException;
use Descope\SDK\Exception\ValidationException;
use Descope\SDK\EndpointsV1;
use Descope\SDK\EndpointsV2;
use Descope\SDK\Token\Extractor;
use Descope\SDK\Management\MgmtV1;

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
        $cache = new class implements CacheInterface {
            public function get(string $key)
            {
                return [
                    'keys' => [[
                        'kid' => 'legit-key',
                        'kty' => 'RSA',
                        'n' => 'sXchf9VHkhPcxP3YXyUbKBo1hTvA2gBC2fD31cstjYb9dyG_rMXVNth5f-vY95bkXICnpPxPId2MnCpbne-Yj1FcGj8JM_2v3ERds43Le2psCIfDOtkKw_S01qK2JfUCpyXibSZ9OmUekR74y15I4z6w_sROF1Et1YYAfR8s',
                        'e' => 'AQAB'
                    ]]
                ];
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

        $extractor = new Extractor(new SDKConfig(['projectId' => 'test_project_id'], $cache));
        $token = $this->jwt(
            ['alg' => 'RS256', 'typ' => 'JWT', 'kid' => 'legit-key'],
            ['sub' => 'attacker', 'roles' => ['admin'], 'exp' => time() + 3600],
            'not_a_real_rsa_signature'
        );

        $this->expectException(TokenException::class);
        $extractor->getClaims($token);
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

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
