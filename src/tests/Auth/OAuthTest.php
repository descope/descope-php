<?php

namespace Descope\Tests\Auth;

use PHPUnit\Framework\TestCase;
use Descope\SDK\Auth\OAuth;
use Descope\SDK\API;
use Descope\SDK\EndpointsV1;
use Descope\SDK\LoginOptions;
use Descope\SDK\Exception\AuthException;

final class OAuthTest extends TestCase
{
    private $apiMock;
    private $oauth;

    protected function setUp(): void
    {
        $this->apiMock = $this->createMock(API::class);
        $this->oauth = new OAuth($this->apiMock);
        EndpointsV1::setBaseUrlFromString('https://api.descope.com');
    }

    public function testStartWithProviderOnly(): void
    {
        $provider = 'google';
        $response = ['url' => 'https://oauth.provider.com/auth'];

        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with(
                $this->stringContains('/v1/auth/oauth/authorize?provider=google'),
                [],
                false,
                null
            )
            ->willReturn($response);

        $result = $this->oauth->start($provider);

        $this->assertIsArray($result);
        $this->assertEquals($response, $result);
    }

    public function testStartWithProviderAndReturnUrl(): void
    {
        $provider = 'facebook';
        $returnUrl = 'http://example.com/callback';
        $response = ['url' => 'https://oauth.provider.com/auth'];

        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with(
                $this->stringContains('/v1/auth/oauth/authorize'),
                $this->anything(),
                false,
                null
            )
            ->willReturn($response);

        $result = $this->oauth->start($provider, $returnUrl);

        $this->assertIsArray($result);
        $this->assertEquals($response, $result);
    }

    public function testStartWithLoginOptions(): void
    {
        $provider = 'google';
        $returnUrl = 'http://example.com/callback';
        $loginOptions = new LoginOptions(false, false, null, null);
        $response = ['url' => 'https://oauth.provider.com/auth'];

        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with(
                $this->anything(),
                $this->callback(function ($body) {
                    return isset($body['stepup']) && isset($body['mfa']) &&
                           $body['stepup'] === false && $body['mfa'] === false;
                }),
                false,
                null
            )
            ->willReturn($response);

        $result = $this->oauth->start($provider, $returnUrl, $loginOptions);

        $this->assertIsArray($result);
    }

    public function testStartWithLoginOptionsAndCustomClaims(): void
    {
        $provider = 'google';
        $returnUrl = 'http://example.com/callback';
        $loginOptions = new LoginOptions(
            false,
            false,
            ['k1' => 'v1'],
            null
        );
        $response = ['url' => 'https://oauth.provider.com/auth'];

        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with(
                $this->anything(),
                $this->callback(function ($body) {
                    return isset($body['customClaims']) &&
                           $body['customClaims']['k1'] === 'v1';
                }),
                false,
                null
            )
            ->willReturn($response);

        $result = $this->oauth->start($provider, $returnUrl, $loginOptions);

        $this->assertIsArray($result);
    }

    public function testStartWithLoginOptionsStepupAndRefreshToken(): void
    {
        $provider = 'facebook';
        $returnUrl = 'http://test.me';
        $loginOptions = new LoginOptions(true, false, null, null);
        $refreshToken = 'refresh_token_123';
        $response = ['url' => 'https://oauth.provider.com/auth'];

        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with(
                $this->anything(),
                $this->callback(function ($body) {
                    return isset($body['stepup']) && $body['stepup'] === true;
                }),
                false,
                $refreshToken
            )
            ->willReturn($response);

        $result = $this->oauth->start($provider, $returnUrl, $loginOptions, $refreshToken);

        $this->assertIsArray($result);
    }

    public function testStartWithLoginOptionsMfaAndRefreshToken(): void
    {
        $provider = 'google';
        $returnUrl = 'http://test.me';
        $loginOptions = new LoginOptions(false, true, null, null);
        $refreshToken = 'refresh_token_123';
        $response = ['url' => 'https://oauth.provider.com/auth'];

        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with(
                $this->anything(),
                $this->callback(function ($body) {
                    return isset($body['mfa']) && $body['mfa'] === true;
                }),
                false,
                $refreshToken
            )
            ->willReturn($response);

        $result = $this->oauth->start($provider, $returnUrl, $loginOptions, $refreshToken);

        $this->assertIsArray($result);
    }

    public function testStartThrowsExceptionOnEmptyProvider(): void
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Unknown OAuth provider');

        $this->oauth->start('');
    }

    public function testStartThrowsExceptionOnStepupWithoutRefreshToken(): void
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Missing refresh token for stepup/mfa');

        $provider = 'google';
        $loginOptions = new LoginOptions(true, false, null, null);

        $this->oauth->start($provider, '', $loginOptions, null);
    }

    public function testStartThrowsExceptionOnMfaWithoutRefreshToken(): void
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Missing refresh token for stepup/mfa');

        $provider = 'facebook';
        $loginOptions = new LoginOptions(false, true, null, null);

        $this->oauth->start($provider, '', $loginOptions, null);
    }

    public function testExchangeToken(): void
    {
        $code = 'c1';
        $response = [
            'sessionJwt' => 'session_jwt_token',
            'refreshJwt' => 'refresh_jwt_token',
            'user' => [
                'loginIds' => ['user@example.com'],
                'email' => 'user@example.com'
            ],
            'firstSeen' => false
        ];

        $jwtResponse = [
            'sessionToken' => 'session_jwt_token',
            'refreshToken' => 'refresh_jwt_token',
            'user' => [
                'loginIds' => ['user@example.com'],
                'email' => 'user@example.com'
            ],
            'firstSeen' => false
        ];

        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with(
                EndpointsV1::$OAUTH_EXCHANGE_TOKEN_PATH,
                ['code' => $code],
                false,
                null
            )
            ->willReturn($response);

        $this->apiMock->expects($this->once())
            ->method('generateJwtResponse')
            ->with($response, 'refresh_jwt_token', null)
            ->willReturn($jwtResponse);

        $result = $this->oauth->exchangeToken($code);

        $this->assertIsArray($result);
        $this->assertEquals($jwtResponse, $result);
    }

    public function testExchangeTokenThrowsExceptionOnEmptyCode(): void
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('code cannot be empty');

        $this->oauth->exchangeToken('');
    }

    public function testComposeStartUrlWithProviderOnly(): void
    {
        // Test indirectly through start method
        $provider = 'google';
        $response = ['url' => 'https://oauth.provider.com/auth'];

        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with(
                $this->callback(function ($uri) use ($provider) {
                    return strpos($uri, '/v1/auth/oauth/authorize') !== false &&
                           strpos($uri, 'provider=' . $provider) !== false &&
                           strpos($uri, 'redirectURL') === false;
                }),
                [],
                false,
                null
            )
            ->willReturn($response);

        $this->oauth->start($provider);
    }

    public function testComposeStartUrlWithProviderAndReturnUrl(): void
    {
        // Test indirectly through start method
        $provider = 'facebook';
        $returnUrl = 'http://example.com/callback';
        $response = ['url' => 'https://oauth.provider.com/auth'];

        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with(
                $this->callback(function ($uri) use ($provider, $returnUrl) {
                    return strpos($uri, '/v1/auth/oauth/authorize') !== false &&
                           strpos($uri, 'provider=' . $provider) !== false &&
                           strpos($uri, 'redirectURL=') !== false;
                }),
                $this->anything(),
                false,
                null
            )
            ->willReturn($response);

        $this->oauth->start($provider, $returnUrl);
    }

    public function testComposeStartBodyWithNullLoginOptions(): void
    {
        // Test indirectly through start method
        $provider = 'google';
        $response = ['url' => 'https://oauth.provider.com/auth'];

        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with(
                $this->anything(),
                [],
                false,
                null
            )
            ->willReturn($response);

        $this->oauth->start($provider, '', null);
    }

    public function testComposeStartBodyWithLoginOptions(): void
    {
        // Test indirectly through start method
        $provider = 'google';
        $loginOptions = new LoginOptions(
            true,
            true,
            ['custom' => 'claim'],
            ['template' => 'option']
        );
        $refreshToken = 'refresh_token_for_stepup_mfa';
        $response = ['url' => 'https://oauth.provider.com/auth'];

        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with(
                $this->anything(),
                $this->callback(function ($body) {
                    return isset($body['stepup']) && $body['stepup'] === true &&
                           isset($body['mfa']) && $body['mfa'] === true &&
                           isset($body['customClaims']) &&
                           isset($body['templateOptions']);
                }),
                false,
                $refreshToken
            )
            ->willReturn($response);

        $this->oauth->start($provider, '', $loginOptions, $refreshToken);
    }

    public function testVerifyProviderReturnsTrueForValidProvider(): void
    {
        // Test indirectly through start method - should not throw exception
        $provider = 'google';
        $response = ['url' => 'https://oauth.provider.com/auth'];

        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->willReturn($response);

        $result = $this->oauth->start($provider);

        $this->assertIsArray($result);
        // If we get here without exception, provider was validated
    }

    public function testStartWithGoogleProvider(): void
    {
        $provider = 'google';
        $response = ['url' => 'https://oauth.google.com/auth'];

        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->willReturn($response);

        $result = $this->oauth->start($provider);

        $this->assertIsArray($result);
    }

    public function testStartWithFacebookProvider(): void
    {
        $provider = 'facebook';
        $response = ['url' => 'https://oauth.facebook.com/auth'];

        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->willReturn($response);

        $result = $this->oauth->start($provider);

        $this->assertIsArray($result);
    }

    public function testExchangeTokenWithValidResponse(): void
    {
        $code = 'valid_code_123';
        $response = [
            'sessionJwt' => 'eyJhbGciOiJFUzM4NCIsImtpZCI6IjJCdDVXTGNjTFVleTFEcDd1dHB0WmIzRng5SyIsInR5cCI6IkpXVCJ9',
            'refreshJwt' => 'refresh_token_here',
            'user' => [
                'loginIds' => ['guyp@descope.com'],
                'name' => '',
                'email' => 'guyp@descope.com',
                'phone' => '',
                'verifiedEmail' => true,
                'verifiedPhone' => false
            ],
            'firstSeen' => false
        ];

        $jwtResponse = [
            'sessionToken' => $response['sessionJwt'],
            'refreshToken' => $response['refreshJwt'],
            'user' => $response['user'],
            'firstSeen' => false
        ];

        $this->apiMock->expects($this->once())
            ->method('doPost')
            ->with(
                EndpointsV1::$OAUTH_EXCHANGE_TOKEN_PATH,
                ['code' => $code],
                false,
                null
            )
            ->willReturn($response);

        $this->apiMock->expects($this->once())
            ->method('generateJwtResponse')
            ->with($response, $response['refreshJwt'], null)
            ->willReturn($jwtResponse);

        $result = $this->oauth->exchangeToken($code);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sessionToken', $result);
        $this->assertArrayHasKey('refreshToken', $result);
        $this->assertArrayHasKey('user', $result);
    }
}
