<?php

namespace Descope\Tests;

use PHPUnit\Framework\TestCase;
use Descope\SDK\EndpointsV1;
use Descope\SDK\EndpointsV2;
use Descope\SDK\Management\MgmtV1;

final class EndpointsTest extends TestCase
{
    public function testEndpointsV1SetBaseUrlFromString()
    {
        $customBaseUrl = 'https://api-custom.descope.com';
        
        EndpointsV1::setBaseUrlFromString($customBaseUrl);
        
        $this->assertEquals($customBaseUrl, EndpointsV1::$baseUrl);
        
        // Verify that paths are updated correctly
        $this->assertStringStartsWith($customBaseUrl, EndpointsV1::$SIGN_UP_PASSWORD_PATH);
        $this->assertStringStartsWith($customBaseUrl, EndpointsV1::$SIGN_IN_PASSWORD_PATH);
        $this->assertStringStartsWith($customBaseUrl, EndpointsV1::$LOGOUT_PATH);
    }

    public function testEndpointsV1SetBaseUrlFromProjectId()
    {
        $projectId = 'Peuc12z2SP0AQgrqkHCdD7u5fRJ4lOta';
        
        EndpointsV1::setBaseUrl($projectId);
        
        // Should extract region and set appropriate baseUrl
        $this->assertStringContainsString('api.euc1.descope.com', EndpointsV1::$baseUrl);
    }

    public function testEndpointsV2SetBaseUrlFromString()
    {
        $customBaseUrl = 'https://api-eu.descope.com';
        
        EndpointsV2::setBaseUrlFromString($customBaseUrl);
        
        // Verify that the public key path uses the custom baseUrl
        $publicKeyPath = EndpointsV2::getPublicKeyPath();
        $this->assertStringStartsWith($customBaseUrl, $publicKeyPath);
        $this->assertStringEndsWith('/v2/keys', $publicKeyPath);
    }

    public function testEndpointsV2SetBaseUrlFromProjectId()
    {
        $projectId = 'Peuc12z2SP0AQgrqkHCdD7u5fRJ4lOta';
        
        EndpointsV2::setBaseUrl($projectId);
        
        // Should extract region and set appropriate baseUrl
        $publicKeyPath = EndpointsV2::getPublicKeyPath();
        $this->assertStringContainsString('api.euc1.descope.com', $publicKeyPath);
    }

    public function testMgmtV1SetBaseUrlFromString()
    {
        $customBaseUrl = 'https://api-management.descope.com';
        
        MgmtV1::setBaseUrlFromString($customBaseUrl);
        
        // Verify that management paths are updated correctly
        $this->assertStringStartsWith($customBaseUrl, MgmtV1::$TENANT_CREATE_PATH);
        $this->assertStringStartsWith($customBaseUrl, MgmtV1::$TENANT_LOAD_PATH);
        $this->assertStringStartsWith($customBaseUrl, MgmtV1::$AUDIT_SEARCH);
    }

    public function testMgmtV1SetBaseUrlFromProjectId()
    {
        $projectId = 'Peuc12z2SP0AQgrqkHCdD7u5fRJ4lOta';
        
        MgmtV1::setBaseUrl($projectId);
        
        // Should extract region and set appropriate baseUrl
        $this->assertStringContainsString('api.euc1.descope.com', MgmtV1::$TENANT_CREATE_PATH);
    }

    public function testEndpointsV1PathUpdates()
    {
        $baseUrl = 'https://api-test.descope.com';
        EndpointsV1::setBaseUrlFromString($baseUrl);
        
        // Test that all major paths are updated
        $paths = [
            'SIGN_UP_PASSWORD_PATH',
            'SIGN_IN_PASSWORD_PATH',
            'LOGOUT_PATH',
            'LOGOUT_ALL_PATH',
            'ME_PATH',
            'REFRESH_TOKEN_PATH'
        ];
        
        foreach ($paths as $path) {
            $this->assertStringStartsWith($baseUrl, EndpointsV1::$$path);
        }
    }

    public function testEndpointsV2PublicKeyPathFormat()
    {
        $baseUrl = 'https://api-custom.descope.com';
        EndpointsV2::setBaseUrlFromString($baseUrl);
        
        $publicKeyPath = EndpointsV2::getPublicKeyPath();
        $this->assertEquals($baseUrl . '/v2/keys', $publicKeyPath);
    }

    public function testMgmtV1PathUpdates()
    {
        $baseUrl = 'https://api-mgmt.descope.com';
        MgmtV1::setBaseUrlFromString($baseUrl);
        
        // Test that all major management paths are updated
        $paths = [
            'TENANT_CREATE_PATH',
            'TENANT_LOAD_PATH',
            'TENANT_UPDATE_PATH',
            'TENANT_DELETE_PATH',
            'AUDIT_SEARCH',
            'AUDIT_CREATE_EVENT'
        ];
        
        foreach ($paths as $path) {
            $this->assertStringStartsWith($baseUrl, MgmtV1::$$path);
        }
    }
}
