<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;

class AuditTest extends TestCase
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

    public function testSearchAllAudit()
    {
        $result = $this->descopeSDK->management->audit->searchAll([
            'userIds' => ['user1'],
            'actions' => ['login'],
            'noTenants' => false,
            'limit' => 10,
            'page' => 0,
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('audits', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertIsArray($result['audits']);
    }

    public function testCreateAuditWebhook()
    {
        $this->descopeSDK->management->audit->createAuditWebhook([
            'name' => 'my-webhook',
            'url' => 'https://example.com/audit',
        ]);

        // If no exceptions were thrown, the test passes.
        $this->assertTrue(true);
    }
}
