<?php

namespace Descope\Tests;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;
use ReflectionClass;

final class APIDebugTest extends TestCase
{
    private $originalEnvDebug;

    protected function setUp(): void
    {
        // Save original environment variable
        $this->originalEnvDebug = $_ENV['DESCOPE_DEBUG'] ?? null;
        unset($_ENV['DESCOPE_DEBUG']);
    }

    protected function tearDown(): void
    {
        // Restore original environment variable
        if ($this->originalEnvDebug !== null) {
            $_ENV['DESCOPE_DEBUG'] = $this->originalEnvDebug;
        } else {
            unset($_ENV['DESCOPE_DEBUG']);
        }
    }

    /**
     * Test that debug defaults to false and can be enabled via config
     */
    public function testDebugDefaultsToFalseAndCanBeEnabled(): void
    {
        // Test default (disabled)
        $sdk = new DescopeSDK([
            'projectId' => 'test_project_id'
        ]);
        
        $reflection = new ReflectionClass($sdk);
        $apiProperty = $reflection->getProperty('api');
        $apiProperty->setAccessible(true);
        $api = $apiProperty->getValue($sdk);
        
        $apiReflection = new ReflectionClass($api);
        $debugProperty = $apiReflection->getProperty('debug');
        $debugProperty->setAccessible(true);
        
        $this->assertFalse($debugProperty->getValue($api), 'Debug should default to false');
        
        // Test enabled via config
        $sdk2 = new DescopeSDK([
            'projectId' => 'test_project_id',
            'debug' => true
        ]);
        
        $api2 = $apiProperty->getValue($sdk2);
        $this->assertTrue($debugProperty->getValue($api2), 'Debug should be enabled when set in config');
    }

    /**
     * Test that debug can be enabled via environment variable
     */
    public function testDebugCanBeEnabledViaEnvironmentVariable(): void
    {
        $_ENV['DESCOPE_DEBUG'] = 'true';
        $sdk = new DescopeSDK([
            'projectId' => 'test_project_id'
        ]);
        
        $reflection = new ReflectionClass($sdk);
        $apiProperty = $reflection->getProperty('api');
        $apiProperty->setAccessible(true);
        $api = $apiProperty->getValue($sdk);
        
        $apiReflection = new ReflectionClass($api);
        $debugProperty = $apiReflection->getProperty('debug');
        $debugProperty->setAccessible(true);
        
        $this->assertTrue($debugProperty->getValue($api), 'Debug should be enabled when DESCOPE_DEBUG env var is set to "true"');
    }
}

