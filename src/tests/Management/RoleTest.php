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

    public function testCreateBatch()
    {
        $result = $this->descopeSDK->management->role->createBatch([
            ['name' => 'BatchRole1', 'description' => 'desc1'],
            ['name' => 'BatchRole2', 'description' => 'desc2'],
        ]);
        $this->assertIsArray($result);
    }

    public function testUpdateBatch()
    {
        $result = $this->descopeSDK->management->role->updateBatch([
            ['name' => 'BatchRole1', 'newName' => 'BatchRole1Renamed', 'description' => 'desc'],
        ]);
        $this->assertIsArray($result);
    }

    public function testUpdateWithId()
    {
        $this->descopeSDK->management->role->create('WithIdRole', 'desc');
        $all = $this->descopeSDK->management->role->loadAll();
        $id = null;
        foreach ($all['roles'] ?? [] as $role) {
            if (($role['name'] ?? '') === 'WithIdRole') {
                $id = $role['id'] ?? null;
                break;
            }
        }
        if ($id === null) {
            $this->markTestSkipped('Could not resolve role id for updateWithId.');
        }
        $this->descopeSDK->management->role->updateWithId($id, '', 'WithIdRoleRenamed', 'desc');
        $this->descopeSDK->management->role->deleteWithId($id);
        $this->assertTrue(true);
    }

    public function testDeleteBatch()
    {
        $this->descopeSDK->management->role->deleteBatch(['BatchRole1Renamed', 'BatchRole2']);
        $this->assertTrue(true);
    }
}
