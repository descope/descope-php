<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

final class SSOSettingsMockTest extends TestCase
{
    private const SAML_SETTINGS = [
        'idpUrl' => 'https://idp.example.com/sso',
        'entityId' => 'entityId',
        'idpCert' => 'cert',
    ];

    private const SAML_METADATA_SETTINGS = [
        'idpMetadataUrl' => 'https://idp.example.com/metadata.xml',
    ];

    /** @var array */
    private $requests;

    /** @var DescopeSDK */
    private $sdk;

    protected function setUp(): void
    {
        $this->requests = [];
        $mock = new MockHandler([new Response(200, [], '{}')]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($this->requests));

        $this->sdk = new DescopeSDK([
            'projectId' => 'test_project_id',
            'managementKey' => 'test_management_key',
            'httpClient' => new Client(['handler' => $stack]),
        ]);
    }

    public function samlConfigureMethodsProvider(): array
    {
        return [
            'configureSAMLSettings' => ['configureSAMLSettings', self::SAML_SETTINGS, '/v1/mgmt/sso/saml'],
            'configureSAMLSettingsByMetadata' => [
                'configureSAMLSettingsByMetadata',
                self::SAML_METADATA_SETTINGS,
                '/v1/mgmt/sso/saml/metadata',
            ],
        ];
    }

    /**
     * @dataProvider samlConfigureMethodsProvider
     */
    public function testNullAcceptedAudiencesSendsNeitherField(string $method, array $settings, string $path): void
    {
        $this->sdk->management->sso->$method('t1', $settings, null, ['example.com']);

        $body = $this->lastRequestBody($path);
        $this->assertSame($settings, $body['settings']);
        $this->assertArrayNotHasKey('acceptedAudiences', $body['settings']);
        $this->assertArrayNotHasKey('replaceAcceptedAudiences', $body['settings']);
    }

    /**
     * @dataProvider samlConfigureMethodsProvider
     */
    public function testOmittedAcceptedAudiencesSendsNeitherField(string $method, array $settings, string $path): void
    {
        $this->sdk->management->sso->$method('t1', $settings);

        $body = $this->lastRequestBody($path);
        $this->assertSame($settings, $body['settings']);
    }

    /**
     * @dataProvider samlConfigureMethodsProvider
     */
    public function testAcceptedAudiencesListSendsBothFields(string $method, array $settings, string $path): void
    {
        $audiences = ['urn:example:aud', 'https://app.example.com', ''];

        $this->sdk->management->sso->$method('t1', $settings, 'https://example.com/callback', [], $audiences);

        $body = $this->lastRequestBody($path);
        $this->assertSame($audiences, $body['settings']['acceptedAudiences']);
        $this->assertTrue($body['settings']['replaceAcceptedAudiences']);
        foreach ($settings as $key => $value) {
            $this->assertSame($value, $body['settings'][$key]);
        }
        $this->assertSame('https://example.com/callback', $body['redirectUrl']);
    }

    /**
     * @dataProvider samlConfigureMethodsProvider
     */
    public function testEmptyAcceptedAudiencesSendsEmptyJsonArray(string $method, array $settings, string $path): void
    {
        $this->sdk->management->sso->$method('t1', $settings, null, [], []);

        $raw = $this->lastRequestRawBody($path);
        $this->assertStringContainsString('"acceptedAudiences":[]', $raw);
        $this->assertStringContainsString('"replaceAcceptedAudiences":true', $raw);
    }

    /**
     * @dataProvider samlConfigureMethodsProvider
     */
    public function testAcceptedAudiencesWithNonSequentialKeysSendsJsonArray(string $method, array $settings, string $path): void
    {
        $this->sdk->management->sso->$method('t1', $settings, null, [], [3 => 'aud-a', 7 => 'aud-b']);

        $raw = $this->lastRequestRawBody($path);
        $this->assertStringContainsString('"acceptedAudiences":["aud-a","aud-b"]', $raw);
    }

    private function lastRequestRawBody(string $expectedPath): string
    {
        $this->assertCount(1, $this->requests);
        $request = $this->requests[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringEndsWith($expectedPath, $request->getUri()->getPath());

        return (string) $request->getBody();
    }

    private function lastRequestBody(string $expectedPath): array
    {
        return json_decode($this->lastRequestRawBody($expectedPath), true);
    }
}
