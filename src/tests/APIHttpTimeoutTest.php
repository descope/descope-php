<?php

declare(strict_types=1);

namespace Descope\Tests;

use Descope\SDK\API;
use Descope\SDK\Configuration\SDKConfig;
use Descope\SDK\DescopeSDK;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class APIHttpTimeoutTest extends TestCase
{
    private function httpClientOf(object $target): ClientInterface
    {
        $property = (new ReflectionClass($target))->getProperty(
            $target instanceof API ? 'httpClient' : 'client'
        );
        $property->setAccessible(true);
        return $property->getValue($target);
    }

    public function testApiAppliesDefaultTimeoutsToItsClient(): void
    {
        $client = $this->httpClientOf(new API('project', null, false));

        $this->assertSame(60.0, $client->getConfig('timeout'));
        $this->assertSame(10.0, $client->getConfig('connect_timeout'));
    }

    public function testApiAppliesConfiguredRequestTimeout(): void
    {
        $client = $this->httpClientOf(new API('project', null, false, null, 8.0));

        $this->assertSame(8.0, $client->getConfig('timeout'));
        $this->assertSame(10.0, $client->getConfig('connect_timeout'));
    }

    public function testSdkConfigAppliesConfiguredRequestTimeout(): void
    {
        $client = $this->httpClientOf(new SDKConfig(['projectId' => 'project'], null, 8.0));

        $this->assertSame(8.0, $client->getConfig('timeout'));
        $this->assertSame(10.0, $client->getConfig('connect_timeout'));
    }

    public function testInjectedClientIsUsedVerbatimAndNotOverridden(): void
    {
        // A caller-supplied client keeps its own transport configuration.
        $injected = new Client(['timeout' => 3.0]);

        $api = new API('project', null, false, null, 8.0, $injected);
        $this->assertSame($injected, $this->httpClientOf($api));
        $this->assertSame(3.0, $this->httpClientOf($api)->getConfig('timeout'));

        $sdkConfig = new SDKConfig(['projectId' => 'project'], null, 8.0, $injected);
        $this->assertSame($injected, $this->httpClientOf($sdkConfig));
    }

    public function testInjectedClientStillPerformsRequests(): void
    {
        $mock = new MockHandler([new Response(200, [], json_encode(['ok' => true]))]);
        $injected = new Client(['handler' => HandlerStack::create($mock)]);

        $api = new API('project', null, false, null, null, $injected);

        $this->assertSame(['ok' => true], $api->doGet('/v1/test', false));
    }

    public function testDescopeSdkPassesRequestTimeoutThroughToApi(): void
    {
        $sdk = new DescopeSDK(['projectId' => 'project', 'requestTimeout' => 12.5]);

        $apiProp = (new ReflectionClass(DescopeSDK::class))->getProperty('api');
        $apiProp->setAccessible(true);
        $api = $apiProp->getValue($sdk);

        $this->assertSame(12.5, $this->httpClientOf($api)->getConfig('timeout'));
    }

    public function testDescopeSdkAcceptsNumericStringTimeout(): void
    {
        $sdk = new DescopeSDK(['projectId' => 'project', 'requestTimeout' => '15']);

        $apiProp = (new ReflectionClass(DescopeSDK::class))->getProperty('api');
        $apiProp->setAccessible(true);
        $api = $apiProp->getValue($sdk);

        $this->assertSame(15.0, $this->httpClientOf($api)->getConfig('timeout'));
    }

    /**
     * @dataProvider invalidTimeoutProvider
     */
    public function testDescopeSdkRejectsInvalidRequestTimeout($value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DescopeSDK(['projectId' => 'project', 'requestTimeout' => $value]);
    }

    public static function invalidTimeoutProvider(): array
    {
        return [
            'zero' => [0],
            'negative' => [-5],
            'non-numeric string' => ['soon'],
            'boolean' => [true],
        ];
    }

    public function testDescopeSdkRejectsNonClientHttpClient(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DescopeSDK(['projectId' => 'project', 'httpClient' => new \stdClass()]);
    }
}
