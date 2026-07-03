<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;

class SSOSettingsTest extends TestCase
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

    public function testLoadSettings()
    {
        $result = $this->descopeSDK->management->sso->loadSettings('t1');
        $this->assertIsArray($result);
    }

    public function testConfigureOIDCSettings()
    {
        $this->descopeSDK->management->sso->configureOIDCSettings('t1', [
            'name' => 'n',
            'clientId' => 'c',
            'clientSecret' => 's',
        ]);
        $this->assertTrue(true);
    }

    public function testConfigureSAMLSettings()
    {
        $this->descopeSDK->management->sso->configureSAMLSettings('t1', [
            'idpUrl' => 'u',
            'entityId' => 'e',
            'idpCert' => 'cert',
        ]);
        $this->assertTrue(true);
    }

    public function testDeleteSettings()
    {
        $this->descopeSDK->management->sso->deleteSettings('t1');
        $this->assertTrue(true);
    }
}
