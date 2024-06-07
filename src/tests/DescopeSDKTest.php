<?php

namespace Descope\Tests;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;

final class DescopeSDKTest extends TestCase
{
    public $descopeSDK;

    public function setUp(): void
    {
        $descopeSDK = new DescopeSDK(
            [
                'projectId' => "<Descope Project ID>",
                'managementKey' => "<Descope Management Key>" // This can be optional
            ]
        );
    }

    public function testVerify(): void
    {
        $token = '...';
        // $this->assertTrue($this->descopeSDK->verify($token));
    }

    public function getClaims(): void
    {
        $token = '...';
        // $this->assertNotEmpty($this->descopeSDK->getClaims($token));
    }

    public function testUserDetails(): void
    {
        $refresh_token = '...';
        // $this->assertIsArray($this->descopeSDK->getUser($refresh_token));
    }

    public function testPassword(): void
    {
        // $result = $this->descopeSDK->password->signUp("example@descope.com", "Password123!", []);
        // $this->assertIsArray($this->descopeSDK->password->signUp("example@descope.com", "Password123!", []));
    }
}
