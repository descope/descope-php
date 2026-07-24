<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;

class OutboundAppsTest extends TestCase
{
    private DescopeSDK $descopeSDK;

    protected function setUp(): void
    {
        $projectId = $_ENV['DESCOPE_PROJECT_ID'] ?? null;
        $managementKey = $_ENV['DESCOPE_MANAGEMENT_KEY'] ?? null;

        if (empty($projectId) || empty($managementKey)) {
            $this->markTestSkipped('Management integration tests require DESCOPE_PROJECT_ID and DESCOPE_MANAGEMENT_KEY in env.');
        }

        $config = [
            'projectId' => $projectId,
            'managementKey' => $managementKey,
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

    public function testCreateApplication()
    {
        $result = $this->descopeSDK->management->outboundApps->createApplication([
            'name' => 'My Outbound App',
            'description' => 'created via php sdk test',
            'clientId' => 'client123',
            'clientSecret' => 'secret123',
            'defaultScopes' => ['read', 'write'],
        ]);

        $this->assertIsArray($result);
    }

    public function testUpdateApplication()
    {
        $result = $this->descopeSDK->management->outboundApps->updateApplication([
            'id' => 'app123',
            'name' => 'My Outbound App',
            'clientId' => 'client123',
        ], 'newsecret123');

        $this->assertIsArray($result);
    }

    public function testDeleteApplication()
    {
        $this->descopeSDK->management->outboundApps->deleteApplication('app123');
        $this->assertTrue(true);
    }

    public function testLoadApplication()
    {
        $result = $this->descopeSDK->management->outboundApps->loadApplication('app123');
        $this->assertIsArray($result);
    }

    public function testLoadAllApplications()
    {
        $result = $this->descopeSDK->management->outboundApps->loadAllApplications();
        $this->assertIsArray($result);
    }

    public function testFetchLatestUserToken()
    {
        $result = $this->descopeSDK->management->outboundApps->fetchLatestUserToken([
            'appId' => 'app123',
            'userId' => 'user123',
            'tenantId' => 'tenant123',
            'options' => ['withRefreshToken' => true, 'forceRefresh' => false],
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
    }

    public function testFetchTenantToken()
    {
        $result = $this->descopeSDK->management->outboundApps->fetchTenantToken([
            'appId' => 'app123',
            'tenantId' => 'tenant123',
            'scopes' => ['read', 'write'],
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
    }

    public function testFetchLatestTenantToken()
    {
        $result = $this->descopeSDK->management->outboundApps->fetchLatestTenantToken([
            'appId' => 'app123',
            'tenantId' => 'tenant123',
            'options' => ['withRefreshToken' => true],
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
    }

    public function testListAppsWithUserToken()
    {
        $result = $this->descopeSDK->management->outboundApps->listAppsWithUserToken('user123', 'tenant123');
        $this->assertIsArray($result);
    }

    public function testUploadUserApiKey()
    {
        $this->descopeSDK->management->outboundApps->uploadUserApiKey([
            'appId' => 'app123',
            'userId' => 'user123',
            'apiKey' => 'apikey123',
            'tenantId' => 'tenant123',
        ]);
        $this->assertTrue(true);
    }

    public function testUploadTenantApiKey()
    {
        $this->descopeSDK->management->outboundApps->uploadTenantApiKey([
            'appId' => 'app123',
            'tenantId' => 'tenant123',
            'apiKey' => 'apikey123',
        ]);
        $this->assertTrue(true);
    }

    public function testUploadUserToken()
    {
        $this->descopeSDK->management->outboundApps->uploadUserToken([
            'appId' => 'app123',
            'userId' => 'user123',
            'refreshToken' => 'refresh123',
            'accessToken' => 'access123',
            'scopes' => ['read'],
        ]);
        $this->assertTrue(true);
    }

    public function testUploadTenantToken()
    {
        $this->descopeSDK->management->outboundApps->uploadTenantToken([
            'appId' => 'app123',
            'tenantId' => 'tenant123',
            'refreshToken' => 'refresh123',
            'accessToken' => 'access123',
        ]);
        $this->assertTrue(true);
    }

    public function testBatchUploadUserTokens()
    {
        $result = $this->descopeSDK->management->outboundApps->batchUploadUserTokens([
            [
                'appId' => 'app123',
                'userId' => 'user123',
                'refreshToken' => 'refresh123',
            ],
        ]);

        $this->assertIsArray($result);
    }

    public function testBatchUploadTenantTokens()
    {
        $result = $this->descopeSDK->management->outboundApps->batchUploadTenantTokens([
            [
                'appId' => 'app123',
                'tenantId' => 'tenant123',
                'refreshToken' => 'refresh123',
            ],
        ]);

        $this->assertIsArray($result);
    }
}
