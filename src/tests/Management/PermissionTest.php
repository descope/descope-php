<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;

class PermissionTest extends TestCase
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
        $this->descopeSDK->management->permission->create('Read', 'desc');
        $this->assertTrue(true);
    }

    public function testLoadAll()
    {
        $result = $this->descopeSDK->management->permission->loadAll();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('permissions', $result);
    }

    public function testUpdateDelete()
    {
        $this->descopeSDK->management->permission->update('Read', 'ReadOnly');
        $this->descopeSDK->management->permission->delete('ReadOnly');
        $this->assertTrue(true);
    }
}
