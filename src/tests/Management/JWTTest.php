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

    public function testImpersonateStepup()
    {
        $result = $this->descopeSDK->management->jwt->impersonateStepup('imp1', 'login1');
        $this->assertIsString($result);
    }

    public function testStopImpersonation()
    {
        $result = $this->descopeSDK->management->jwt->stopImpersonation('someJwt');
        $this->assertIsString($result);
    }

    public function testSignIn()
    {
        $result = $this->descopeSDK->management->jwt->signIn('login1');
        $this->assertIsArray($result);
    }

    public function testSignUp()
    {
        $result = $this->descopeSDK->management->jwt->signUp('login1', ['email' => 'user@example.com']);
        $this->assertIsArray($result);
    }

    public function testSignUpOrIn()
    {
        $result = $this->descopeSDK->management->jwt->signUpOrIn('login1', ['email' => 'user@example.com']);
        $this->assertIsArray($result);
    }

    public function testAnonymous()
    {
        $result = $this->descopeSDK->management->jwt->anonymous();
        $this->assertIsArray($result);
    }
}
