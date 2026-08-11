<?php

namespace Descope\Tests;

use Descope\SDK\API;
use Descope\SDK\DescopeSDK;
use Descope\SDK\EndpointsV1;
use Descope\SDK\Management\MgmtV1;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Covers the Authorization header the SDK sends: the auth management key rides along with every
 * authentication request so that methods whose public access has been disabled can still be used,
 * and it is never sent on management requests (where the management key is used instead).
 */
final class APIAuthManagementKeyTest extends TestCase
{
    private const PROJECT_ID = 'project';
    private const MANAGEMENT_KEY = 'mgmt-key';
    private const AUTH_MANAGEMENT_KEY = 'auth-key';
    private const REFRESH_TOKEN = 'refresh-token';
    private const ACCESS_KEY = 'access-key';

    /** @var array<int,array<string,mixed>> */
    private $requests = [];

    protected function setUp(): void
    {
        $this->requests = [];
        EndpointsV1::setBaseUrlFromString('https://api.descope.com');
        MgmtV1::setBaseUrlFromString('https://api.descope.com');
    }

    public function testAuthRequestWithoutAuthManagementKey(): void
    {
        $api = $this->api(null, null);
        $api->doPost(EndpointsV1::$SIGN_IN_PASSWORD_PATH, [], false);

        $this->assertAuthorization('Bearer ' . self::PROJECT_ID);
    }

    public function testAuthRequestWithAuthManagementKey(): void
    {
        $api = $this->api(null, self::AUTH_MANAGEMENT_KEY);
        $api->doPost(EndpointsV1::$SIGN_IN_PASSWORD_PATH, [], false);

        $this->assertAuthorization('Bearer ' . self::PROJECT_ID . ':' . self::AUTH_MANAGEMENT_KEY);
    }

    public function testAuthRequestWithRefreshTokenAndAuthManagementKey(): void
    {
        $api = $this->api(null, self::AUTH_MANAGEMENT_KEY);
        $api->doPost(EndpointsV1::$REFRESH_TOKEN_PATH, [], false, self::REFRESH_TOKEN);

        $this->assertAuthorization(
            'Bearer ' . self::PROJECT_ID . ':' . self::REFRESH_TOKEN . ':' . self::AUTH_MANAGEMENT_KEY
        );
    }

    public function testAuthGetRequestWithRefreshTokenAndAuthManagementKey(): void
    {
        $api = $this->api(null, self::AUTH_MANAGEMENT_KEY);
        $api->doGet(EndpointsV1::$ME_PATH, false, self::REFRESH_TOKEN);

        $this->assertAuthorization(
            'Bearer ' . self::PROJECT_ID . ':' . self::REFRESH_TOKEN . ':' . self::AUTH_MANAGEMENT_KEY
        );
    }

    public function testAuthRequestWithRefreshTokenOnly(): void
    {
        $api = $this->api(null, null);
        $api->doPost(EndpointsV1::$REFRESH_TOKEN_PATH, [], false, self::REFRESH_TOKEN);

        $this->assertAuthorization('Bearer ' . self::PROJECT_ID . ':' . self::REFRESH_TOKEN);
    }

    /**
     * An access key is presented in the same position a refresh token is, so it composes with the
     * auth management key the same way. Matches the Java and Python SDKs.
     */
    public function testAccessKeyExchangeCarriesTheAuthManagementKey(): void
    {
        $sdk = $this->sdk(self::AUTH_MANAGEMENT_KEY);
        $sdk->exchangeAccessKey(self::ACCESS_KEY);

        $this->assertAuthorization(
            'Bearer ' . self::PROJECT_ID . ':' . self::ACCESS_KEY . ':' . self::AUTH_MANAGEMENT_KEY
        );
    }

    public function testAccessKeyExchangeWithoutAuthManagementKey(): void
    {
        $sdk = $this->sdk(null);
        $sdk->exchangeAccessKey(self::ACCESS_KEY);

        $this->assertAuthorization('Bearer ' . self::PROJECT_ID . ':' . self::ACCESS_KEY);
    }

    public function testManagementRequestSendsOnlyTheManagementKey(): void
    {
        $api = $this->api(self::MANAGEMENT_KEY, self::AUTH_MANAGEMENT_KEY);
        $api->doPost(MgmtV1::$USER_LOAD_PATH, [], true);

        $this->assertAuthorization('Bearer ' . self::PROJECT_ID . ':' . self::MANAGEMENT_KEY);
    }

    public function testManagementDeleteSendsOnlyTheManagementKey(): void
    {
        $api = $this->api(self::MANAGEMENT_KEY, self::AUTH_MANAGEMENT_KEY);
        $api->doDelete(MgmtV1::$USER_DELETE_PATH);

        $this->assertAuthorization('Bearer ' . self::PROJECT_ID . ':' . self::MANAGEMENT_KEY);
    }

    /**
     * A management call made without a management key falls through to the shared bearer
     * assembly, and must still not pick up the auth management key.
     */
    public function testManagementRequestWithoutManagementKeyDoesNotSendAuthManagementKey(): void
    {
        $api = $this->api(null, self::AUTH_MANAGEMENT_KEY);
        $api->doPost(MgmtV1::$USER_LOAD_PATH, [], true);

        $this->assertAuthorization('Bearer ' . self::PROJECT_ID);
    }

    public function testAuthManagementKeyIsWiredFromSdkConfig(): void
    {
        $sdk = new DescopeSDK([
            'projectId' => self::PROJECT_ID,
            'authManagementKey' => self::AUTH_MANAGEMENT_KEY,
        ]);

        $sdkReflection = new ReflectionClass($sdk);
        $apiProperty = $sdkReflection->getProperty('api');
        $apiProperty->setAccessible(true);
        $api = $apiProperty->getValue($sdk);

        $apiReflection = new ReflectionClass($api);
        $authManagementKeyProperty = $apiReflection->getProperty('authManagementKey');
        $authManagementKeyProperty->setAccessible(true);

        $this->assertSame(self::AUTH_MANAGEMENT_KEY, $authManagementKeyProperty->getValue($api));
    }

    public function testAuthManagementKeyDefaultsToEmptyWhenNotConfigured(): void
    {
        $sdk = new DescopeSDK(['projectId' => self::PROJECT_ID]);

        $sdkReflection = new ReflectionClass($sdk);
        $apiProperty = $sdkReflection->getProperty('api');
        $apiProperty->setAccessible(true);
        $api = $apiProperty->getValue($sdk);

        $apiReflection = new ReflectionClass($api);
        $authManagementKeyProperty = $apiReflection->getProperty('authManagementKey');
        $authManagementKeyProperty->setAccessible(true);

        $this->assertSame('', $authManagementKeyProperty->getValue($api));
    }

    /**
     * Builds an API whose HTTP client records the requests it is handed.
     */
    private function api(?string $managementKey, ?string $authManagementKey): API
    {
        $api = new API(
            self::PROJECT_ID,
            $managementKey,
            false,
            'https://api.descope.com',
            null,
            null,
            $authManagementKey
        );

        $this->captureRequestsOn($api);

        return $api;
    }

    /**
     * Builds a full SDK whose API records the requests it is handed, so tests can go through the
     * public entry points rather than calling the API directly.
     */
    private function sdk(?string $authManagementKey): DescopeSDK
    {
        $config = ['projectId' => self::PROJECT_ID];
        if ($authManagementKey !== null) {
            $config['authManagementKey'] = $authManagementKey;
        }

        $sdk = new DescopeSDK($config);

        $sdkReflection = new ReflectionClass($sdk);
        $apiProperty = $sdkReflection->getProperty('api');
        $apiProperty->setAccessible(true);
        $this->captureRequestsOn($apiProperty->getValue($sdk));

        return $sdk;
    }

    /**
     * Swaps in an HTTP client that answers with a stub response and records every request.
     */
    private function captureRequestsOn(API $api): void
    {
        $stack = HandlerStack::create(new MockHandler([new Response(200, [], json_encode(['ok' => true]))]));
        $stack->push(Middleware::history($this->requests));

        $reflection = new ReflectionClass(API::class);
        $httpClientProp = $reflection->getProperty('httpClient');
        $httpClientProp->setAccessible(true);
        $httpClientProp->setValue($api, new Client(['handler' => $stack]));
    }

    private function assertAuthorization(string $expected): void
    {
        $this->assertCount(1, $this->requests);
        $this->assertSame($expected, $this->requests[0]['request']->getHeaderLine('Authorization'));
    }
}
