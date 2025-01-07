<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;

class AuditTest extends TestCase
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

    public function testSearchAudit()
    {
        $userIds = ['user1'];
        $actions = ['login'];

        // Perform the search
        $result = $this->descopeSDK->management->audit->search($userIds, $actions);

        // Assertions
        $this->assertIsArray($result);
        $this->assertArrayHasKey('audits', $result);
        $this->assertIsArray($result['audits']);

        foreach ($result['audits'] as $audit) {
            $this->assertArrayHasKey('projectId', $audit);
            $this->assertArrayHasKey('userId', $audit);
            $this->assertArrayHasKey('action', $audit);
            $this->assertArrayHasKey('occurred', $audit);
        }
    }

    public function testCreateAuditEvent()
    {
        // Create an audit event
        $this->descopeSDK->management->audit->createEvent(
            'login',
            'info',
            'actor1',
            'tenant1',
            'user1',
            ['key' => 'value']
        );

        // If no exceptions were thrown, the test passes.
        $this->assertTrue(true);
    }

    public function testCreateAuditEventWithoutUserId()
    {
        // Create an audit event without a userId
        $this->descopeSDK->management->audit->createEvent(
            'login',
            'info',
            'actor1',
            'tenant1',
            null,
            ['key' => 'value']
        );

        // If no exceptions were thrown, the test passes.
        $this->assertTrue(true);
    }
}
