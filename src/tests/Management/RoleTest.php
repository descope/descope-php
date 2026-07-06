<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;

class RoleTest extends TestCase
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
        $this->descopeSDK->management->role->create('My Role', 'desc', ['Read']);
        $this->assertTrue(true);
    }

    public function testLoadAll()
    {
        $result = $this->descopeSDK->management->role->loadAll();
        $this->assertIsArray($result);
    }

    public function testSearch()
    {
        $result = $this->descopeSDK->management->role->search();
        $this->assertIsArray($result);
    }

    public function testUpdateDelete()
    {
        $this->descopeSDK->management->role->update('My Role', 'Renamed');
        $this->descopeSDK->management->role->delete('Renamed');
        $this->assertTrue(true);
    }
}
