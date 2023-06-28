<?php

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;

final class DescopeSDKTest extends TestCase
{
    private $descopeSDK;

    protected function setUp(): void
    {
        $descopeSDK = new DescopeSDK([
            'projectId' => $_ENV['DESCOPE_PROJECT_ID']
        ]);
    }

    public function testVerify(): void
    {
        $token = '...';
        $this->assertTrue($this->descopeSDK->verify($token));

        $token = '...';
        $this->assertFalse($this->descopeSDK->verify($token));
    }

    public function getClaims(): void
    {
        $token = '...';
        $this->assertTrue($this->descopeSDK->getClaims($token));

        $token = '...';
        $this->assertFalse($this->descopeSDK->getClaims($token));
    }
}