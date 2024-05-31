<?php

use PHPUnit\Framework\TestCase;
use Descope\SDK\API;
use Descope\SDK\Management\Audit;
use Descope\SDK\Management\MgmtV1;

class AuditTest extends TestCase
{
    private $apiMock;
    private $audit;

    protected function setUp(): void
    {
        $this->apiMock = $this->createMock(API::class);
        $this->audit = new Audit($this->apiMock);
    }

    public function testSearch()
    {
        $response = [
            'audits' => [
                [
                    'projectId' => 'project1',
                    'userId' => 'user1',
                    'action' => 'login',
                    'occurred' => 1650000000000,
                    'device' => 'mobile',
                    'method' => 'otp',
                    'geo' => 'US',
                    'remoteAddress' => '192.168.1.1',
                    'externalIds' => ['login1'],
                    'tenants' => ['tenant1'],
                    'data' => ['key' => 'value']
                ]
            ]
        ];

        $this->apiMock
            ->expects($this->once())
            ->method('doPost')
            ->with(MgmtV1::AUDIT_SEARCH, $this->anything(), true)
            ->willReturn($response);

        $result = $this->audit->search(['user1'], ['login']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('audits', $result);
        $this->assertCount(1, $result['audits']);
        $this->assertEquals('project1', $result['audits'][0]['projectId']);
        $this->assertEquals('user1', $result['audits'][0]['userId']);
    }

    public function testCreateEvent()
    {
        $this->apiMock
            ->expects($this->once())
            ->method('doPost')
            ->with(
                MgmtV1::AUDIT_CREATE_EVENT,
                [
                    'action' => 'login',
                    'type' => 'info',
                    'actorId' => 'actor1',
                    'tenantId' => 'tenant1',
                    'userId' => 'user1',
                    'data' => ['key' => 'value']
                ],
                true
            );

        $this->audit->createEvent('login', 'info', 'actor1', 'tenant1', 'user1', ['key' => 'value']);
    }

    public function testConvertAuditRecord()
    {
        $auditRecord = [
            'projectId' => 'project1',
            'userId' => 'user1',
            'action' => 'login',
            'occurred' => 1650000000000,
            'device' => 'mobile',
            'method' => 'otp',
            'geo' => 'US',
            'remoteAddress' => '192.168.1.1',
            'externalIds' => ['login1'],
            'tenants' => ['tenant1'],
            'data' => ['key' => 'value']
        ];

        $result = $this->audit->convertAuditRecord($auditRecord);

        $this->assertIsArray($result);
        $this->assertEquals('project1', $result['projectId']);
        $this->assertEquals('user1', $result['userId']);
        $this->assertEquals('login', $result['action']);
        $this->assertInstanceOf(DateTime::class, $result['occurred']);
        $this->assertEquals('mobile', $result['device']);
        $this->assertEquals('otp', $result['method']);
        $this->assertEquals('US', $result['geo']);
        $this->assertEquals('192.168.1.1', $result['remoteAddress']);
        $this->assertEquals(['login1'], $result['loginIds']);
        $this->assertEquals(['tenant1'], $result['tenants']);
        $this->assertEquals(['key' => 'value'], $result['data']);
    }
}

?>
