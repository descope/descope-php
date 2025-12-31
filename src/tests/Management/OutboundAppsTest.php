<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;

class OutboundAppsTest extends TestCase
{
    private DescopeSDK $descopeSDK;

    protected function setUp(): void
    {
        $config = [
            'projectId' => 'descope_project_id',
            'managementKey' => 'descope_management_key',
        ];

        $this->descopeSDK = new DescopeSDK($config);
    }

    public function testFetchUserToken()
    {
        $result = $this->descopeSDK->management->outboundApps->fetchUserToken(
            'app123',
            'user123',
            ['read', 'write'],
            true,
            false,
            'tenant123'
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
        $this->assertIsArray($result['token']);
        $this->assertArrayHasKey('id', $result['token']);
        $this->assertArrayHasKey('appId', $result['token']);
        $this->assertArrayHasKey('userId', $result['token']);
        $this->assertArrayHasKey('accessToken', $result['token']);
        $this->assertArrayHasKey('scopes', $result['token']);
    }

    public function testFetchUserTokenMinimalParams()
    {
        $result = $this->descopeSDK->management->outboundApps->fetchUserToken(
            'app123',
            'user123'
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
    }

    public function testDeleteUserTokensByAppId()
    {
        $this->descopeSDK->management->outboundApps->deleteUserTokens('app123', null);
        $this->assertTrue(true);
    }

    public function testDeleteUserTokensByUserId()
    {
        $this->descopeSDK->management->outboundApps->deleteUserTokens(null, 'user123');
        $this->assertTrue(true);
    }

    public function testDeleteUserTokensByBoth()
    {
        $this->descopeSDK->management->outboundApps->deleteUserTokens('app123', 'user123');
        $this->assertTrue(true);
    }

    public function testDeleteTokenById()
    {
        $this->descopeSDK->management->outboundApps->deleteTokenById('token123');
        $this->assertTrue(true);
    }
}
