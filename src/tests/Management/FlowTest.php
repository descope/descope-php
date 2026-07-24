<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;

class FlowTest extends TestCase
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

    public function testListFlows()
    {
        $result = $this->descopeSDK->management->flow->listFlows();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('flows', $result);
    }

    public function testExportImportFlow()
    {
        $exported = $this->descopeSDK->management->flow->exportFlow('sign-up-or-in');
        $this->assertIsArray($exported);

        $imported = $this->descopeSDK->management->flow->importFlow('sign-up-or-in', []);
        $this->assertIsArray($imported);
    }

    public function testDelete()
    {
        $this->descopeSDK->management->flow->delete(['f1']);
        $this->assertTrue(true);
    }

    public function testExportImportTheme()
    {
        $exported = $this->descopeSDK->management->flow->exportTheme();
        $this->assertIsArray($exported);

        $imported = $this->descopeSDK->management->flow->importTheme([]);
        $this->assertIsArray($imported);
    }

    public function testRunManagementFlow()
    {
        $result = $this->descopeSDK->management->flow->runManagementFlow(
            'sign-up-or-in',
            ['input' => [], 'preview' => false]
        );
        $this->assertIsArray($result);
    }

    public function testRunManagementFlowAsync()
    {
        $result = $this->descopeSDK->management->flow->runManagementFlowAsync(
            'sign-up-or-in',
            ['input' => []]
        );
        $this->assertIsArray($result);
    }

    public function testGetManagementFlowAsyncResult()
    {
        $result = $this->descopeSDK->management->flow->getManagementFlowAsyncResult('execution-id');
        $this->assertIsArray($result);
    }

    public function testDeleteFlows()
    {
        $this->descopeSDK->management->flow->deleteFlows(['f1', 'f2']);
        $this->assertTrue(true);
    }
}
