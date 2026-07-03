<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;

class TenantTest extends TestCase
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
        $result = $this->descopeSDK->management->tenant->create('My Tenant');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
    }

    public function testLoadAll()
    {
        $result = $this->descopeSDK->management->tenant->loadAll();
        $this->assertIsArray($result);
    }

    public function testSearchAll()
    {
        $result = $this->descopeSDK->management->tenant->searchAll();
        $this->assertIsArray($result);
    }

    public function testLoadUpdateDelete()
    {
        $this->descopeSDK->management->tenant->load('t1');
        $this->descopeSDK->management->tenant->update('t1', 'New');
        $this->descopeSDK->management->tenant->delete('t1');
        $this->assertTrue(true);
    }
}
