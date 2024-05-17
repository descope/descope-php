<?php

namespace Descope\SDK\Tests\Auth;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;
use Descope\SDK\Auth\SSO;
use Descope\Exception\AuthException;

final class SSOTest extends TestCase
{
    private $descopeSDK;
    private $sso;

    protected function setUp(): void
    {
        $projectId = 'project-id';
        $managementKey = 'management-key';
        $descopeSDK = new DescopeSDK([
            'projectId' => $projectId
        ]);
        $this->sso = new SSO($this->descopeSDK->getAuth());
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

        // Mock the HTTP client response
        $mockResponse = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $mockResponse->method('getBody')->willReturn(json_encode($response));
        $this->descopeSDK->getAuth()->method('doPost')->willReturn($mockResponse);

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

        // Mock the HTTP client response
        $mockResponse = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $mockResponse->method('getBody')->willReturn(json_encode($response));
        $this->descopeSDK->getAuth()->method('doPost')->willReturn($mockResponse);

        $result = $this->sso->ssoExchangeToken($code);

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