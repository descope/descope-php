<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;
use Descope\SDK\Management\Management;

/**
 * Verifies that every management component is wired up and exposes the
 * methods required for parity with the Descope Go/Python SDKs. Runs without
 * credentials — it only inspects the object graph, never the network.
 */
class ManagementParityTest extends TestCase
{
    private Management $management;

    protected function setUp(): void
    {
        $sdk = new DescopeSDK([
            'projectId' => 'Ptest',
            'managementKey' => 'Ktest',
        ]);
        $this->management = $sdk->management;
    }

    public function testAllComponentsAreWired(): void
    {
        $components = [
            'user' => \Descope\SDK\Management\User::class,
            'audit' => \Descope\SDK\Management\Audit::class,
            'outboundApps' => \Descope\SDK\Management\OutboundApps::class,
            'tenant' => \Descope\SDK\Management\Tenant::class,
            'role' => \Descope\SDK\Management\Role::class,
            'permission' => \Descope\SDK\Management\Permission::class,
            'accessKey' => \Descope\SDK\Management\AccessKey::class,
            'ssoApplication' => \Descope\SDK\Management\SSOApplication::class,
            'sso' => \Descope\SDK\Management\SSOSettings::class,
            'jwt' => \Descope\SDK\Management\JWT::class,
            'flow' => \Descope\SDK\Management\Flow::class,
        ];

        foreach ($components as $property => $class) {
            $this->assertInstanceOf($class, $this->management->$property);
            $this->assertInstanceOf($class, $this->management->$property());
        }
    }

    /**
     * @dataProvider expectedMethodsProvider
     */
    public function testComponentExposesMethods(string $property, array $methods): void
    {
        $component = $this->management->$property;
        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists($component, $method),
                sprintf('%s is missing method %s()', get_class($component), $method)
            );
        }
    }

    public function expectedMethodsProvider(): array
    {
        return [
            'tenant' => ['tenant', ['create', 'update', 'delete', 'load', 'loadAll', 'searchAll']],
            'role' => ['role', ['create', 'update', 'delete', 'loadAll', 'search']],
            'permission' => ['permission', ['create', 'update', 'delete', 'loadAll']],
            'accessKey' => ['accessKey', ['create', 'load', 'searchAll', 'update', 'activate', 'deactivate', 'delete']],
            'ssoApplication' => ['ssoApplication', [
                'createOidcApplication', 'createSamlApplication',
                'updateOidcApplication', 'updateSamlApplication',
                'delete', 'load', 'loadAll',
            ]],
            'sso' => ['sso', [
                'loadSettings', 'configureOIDCSettings', 'configureSAMLSettings',
                'configureSAMLSettingsByMetadata', 'deleteSettings',
            ]],
            'jwt' => ['jwt', ['updateJWT', 'impersonate']],
            'flow' => ['flow', ['listFlows', 'delete', 'exportFlow', 'importFlow', 'exportTheme', 'importTheme']],
        ];
    }
}
