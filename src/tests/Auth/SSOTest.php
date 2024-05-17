<?php

namespace Descope\SDK\Tests\Auth;

use PHPUnit\Framework\TestCase;
use Descope\SDK\Auth\SSO;
use Descope\Exception\AuthException;
use Descope\SDK\API;
use Psr\Http\Message\ResponseInterface;

final class SSOTest extends TestCase
{
    private $authMock;
    private $sso;

    protected function setUp(): void
    {
        $this->authMock = $this->createMock(API::class);
        $this->sso = new SSO($this->authMock);
    }

    public function testSSOSignIn(): void
    {
        $tenant = 'example-tenant';
        $redirectUrl = 'https://example.com/callback';
        $prompt = 'login';
        $stepup = true;
        $mfa = true;
        $customClaims = ['claim1' => 'value1'];
        $ssoAppId = 'ssoApp123';

        $response = [
            'jwt' => 'fake_jwt',
            'refreshToken' => 'fake_refresh_token',
            'sessionToken' => 'fake_session_token'
        ];

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getBody')->willReturn(json_encode($response));

        $this->authMock->expects($this->once())
            ->method('doPost')
            ->willReturn($mockResponse);

        $result = $this->sso->signIn($tenant, $redirectUrl, $prompt, $stepup, $mfa, $customClaims, $ssoAppId);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('jwt', $result);
        $this->assertArrayHasKey('refreshToken', $result);
        $this->assertArrayHasKey('sessionToken', $result);
        $this->assertEquals($response['jwt'], $result['jwt']);
    }

    public function testSSOExchangeToken(): void
    {
        $code = 'exchange_code';
        $response = [
            'jwt' => 'fake_jwt',
            'refreshToken' => 'fake_refresh_token',
            'sessionToken' => 'fake_session_token'
        ];

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getBody')->willReturn(json_encode($response));

        $this->authMock->expects($this->once())
            ->method('doPost')
            ->willReturn($mockResponse);

        $result = $this->sso->exchangeToken($code);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('jwt', $result);
        $this->assertArrayHasKey('refreshToken', $result);
        $this->assertArrayHasKey('sessionToken', $result);
        $this->assertEquals($response['jwt'], $result['jwt']);
    }

    public function testSSOSignInThrowsExceptionOnEmptyTenant(): void
    {
        $this->expectException(AuthException::class);
        $this->sso->signIn('', 'https://example.com/callback');
    }

    public function testSSOSignInThrowsExceptionOnEmptyRedirectUrl(): void
    {
        $this->expectException(AuthException::class);
        $this->sso->signIn('example-tenant', '');
    }
}