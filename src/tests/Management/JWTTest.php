<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;

class JWTTest extends TestCase
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

    public function testUpdateJWT()
    {
        $result = $this->descopeSDK->management->jwt->updateJWT('someJwt', ['k' => 'v']);
        $this->assertIsString($result);
    }

    public function testImpersonate()
    {
        $result = $this->descopeSDK->management->jwt->impersonate('imp1', 'login1');
        $this->assertIsString($result);
    }
}
