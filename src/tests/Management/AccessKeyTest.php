<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;

class AccessKeyTest extends TestCase
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

    public function testCreate()
    {
        $result = $this->descopeSDK->management->accessKey->create('My Key');
        $this->assertIsArray($result);
    }

    public function testSearchAll()
    {
        $result = $this->descopeSDK->management->accessKey->searchAll();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('keys', $result);
    }

    public function testLoadUpdate()
    {
        $this->descopeSDK->management->accessKey->load('k1');
        $this->descopeSDK->management->accessKey->update('k1', 'Renamed');
        $this->assertTrue(true);
    }

    public function testActivateDeactivateDelete()
    {
        $this->descopeSDK->management->accessKey->activate('k1');
        $this->descopeSDK->management->accessKey->deactivate('k1');
        $this->descopeSDK->management->accessKey->delete('k1');
        $this->assertTrue(true);
    }
}
