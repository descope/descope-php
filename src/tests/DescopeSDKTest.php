<?php

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;
use Descope\SDK\API;
use Descope\SDK\Configuration\SDKConfig;

class DescopeSDKTest extends TestCase
{
    private $config;
    private $sdk;

    protected function setUp(): void
    {
        $this->config = [
            'projectId' => 'test_project_id',
            'managementKey' => 'test_management_key'
        ];
        $this->sdk = new DescopeSDK($this->config);
    }

    public function testConstructorInitializesComponents()
    {
        $this->assertInstanceOf(SDKConfig::class, $this->sdk->password());
        $this->assertInstanceOf(SDKConfig::class, $this->sdk->sso());
        $this->assertInstanceOf(SDKConfig::class, $this->sdk->management());
    }

    public function testVerifyThrowsExceptionWithoutToken()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sdk->verify(null);
    }

    public function testRefreshSessionThrowsExceptionWithoutToken()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sdk->refreshSession(null);
    }
}