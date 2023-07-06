<?php

namespace Descope\SDK\Tests;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;

final class DescopeSDKTest extends TestCase
{
    private $descopeSDK;

    protected function setUp(): void
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();

        $descopeSDK = new DescopeSDK([
            'projectId' => $_ENV['DESCOPE_PROJECT_ID']
        ]);
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
}
