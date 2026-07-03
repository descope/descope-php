<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;

class SSOApplicationTest extends TestCase
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

    public function testCreateOidcApplication()
    {
        $result = $this->descopeSDK->management->ssoApplication->createOidcApplication(
            'My App',
            'https://login.example.com'
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
    }

    public function testCreateSamlApplication()
    {
        $result = $this->descopeSDK->management->ssoApplication->createSamlApplication(
            'My SAML',
            'https://login.example.com'
        );

        $this->assertIsArray($result);
    }

    public function testLoadAll()
    {
        $result = $this->descopeSDK->management->ssoApplication->loadAll();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('apps', $result);
    }

    public function testLoadDelete()
    {
        $this->descopeSDK->management->ssoApplication->load('app1');
        $this->descopeSDK->management->ssoApplication->delete('app1');
        $this->assertTrue(true);
    }
}
