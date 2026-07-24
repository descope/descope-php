<?php

declare(strict_types=1);

namespace Descope\Tests;

use Descope\SDK\API;
use Descope\SDK\Configuration\HttpClientConfig;
use Descope\SDK\Configuration\SDKConfig;
use Descope\SDK\DescopeSDK;
use Descope\SDK\Exception\AuthException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class APIHttpTimeoutTest extends TestCase
{
    public function testDefaultTimeoutsAreBounded(): void
    {
        $config = HttpClientConfig::fromArray([]);

        $this->assertSame(60.0, $config->requestTimeout());
        $this->assertSame(60.0, $config->requestTimeout(true));
        $this->assertSame(10.0, $config->connectTimeout());
    }

    public function testAuthenticationRequestUsesConfiguredTimeouts(): void
    {
        $requests = [];
        $api = $this->apiWithResponses(
            new HttpClientConfig(8.0, 30.0, 2.0),
            [new Response(200, [], json_encode(['ok' => true]))],
            $requests
        );

        $this->assertSame(['ok' => true], $api->doGet('/v1/test', false));
        $this->assertEqualsWithDelta(8.0, $requests[0]['options']['timeout'], 0.01);
        $this->assertSame(2.0, $requests[0]['options']['connect_timeout']);
    }

    public function testManagementRequestUsesConfiguredOverride(): void
    {
        $requests = [];
        $api = $this->apiWithResponses(
            new HttpClientConfig(8.0, 45.0, 2.0),
            [new Response(200, [], json_encode(['ok' => true]))],
            $requests
        );

        $this->assertSame(['ok' => true], $api->doGet('/v1/test', true));
        $this->assertEqualsWithDelta(45.0, $requests[0]['options']['timeout'], 0.01);
        $this->assertSame(2.0, $requests[0]['options']['connect_timeout']);
    }

    public function testRetryReceivesOnlyTheRemainingOverallBudget(): void
    {
        $requests = [];
        $api = $this->apiWithResponses(
            new HttpClientConfig(8.0, null, 2.0),
            [
                $this->retryableException(503),
                new Response(200, [], json_encode(['ok' => true])),
            ],
            $requests
        );
        $this->setRetryDelays($api, [100000]);

        $this->assertSame(['ok' => true], $api->doGet('/v1/test', false));
        $this->assertEqualsWithDelta(8.0, $requests[0]['options']['timeout'], 0.01);
        $this->assertEqualsWithDelta(7.9, $requests[1]['options']['timeout'], 0.02);
    }

    public function testRetryIsSkippedWhenBackoffWouldExhaustTheBudget(): void
    {
        $requests = [];
        $api = $this->apiWithResponses(
            new HttpClientConfig(0.05, null, 0.01),
            [
                $this->retryableException(503),
                new Response(200, [], json_encode(['ok' => true])),
            ],
            $requests
        );
        $this->setRetryDelays($api, [100000]);

        try {
            $api->doGet('/v1/test', false);
            $this->fail('Expected the retryable response to be surfaced when the retry budget is exhausted.');
        } catch (AuthException $e) {
            $this->assertCount(1, $requests);
        }
    }

    public function testJwksRequestUsesTheAuthenticationTimeout(): void
    {
        $requests = [];
        $handlerStack = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['keys' => []])),
        ]));
        $handlerStack->push(Middleware::history($requests));
        $client = new Client(['handler' => $handlerStack]);
        $httpClientConfig = new HttpClientConfig(12.0, 60.0, 3.0);
        $config = new SDKConfig(
            ['projectId' => 'project'],
            null,
            $httpClientConfig,
            $client
        );

        $this->assertSame(['keys' => []], $config->getJWKSets());
        $this->assertSame(12.0, $requests[0]['options']['timeout']);
        $this->assertSame(3.0, $requests[0]['options']['connect_timeout']);
    }

    public function testSdkPassesTimeoutsAndCustomClientToApi(): void
    {
        $requests = [];
        $handlerStack = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['ok' => true])),
        ]));
        $handlerStack->push(Middleware::history($requests));
        $client = new Client(['handler' => $handlerStack]);
        $sdk = new DescopeSDK([
            'projectId' => 'project',
            'requestTimeout' => 8,
            'managementRequestTimeout' => 45,
            'connectTimeout' => 2,
            'httpClient' => $client,
        ]);

        $this->assertSame(['ok' => true], $sdk->api->doGet('/v1/test', false));
        $this->assertEqualsWithDelta(8.0, $requests[0]['options']['timeout'], 0.01);
        $this->assertSame(2.0, $requests[0]['options']['connect_timeout']);
    }

    /**
     * @dataProvider invalidTimeoutProvider
     */
    public function testInvalidTimeoutConfigurationIsRejected(array $config): void
    {
        $this->expectException(\InvalidArgumentException::class);
        HttpClientConfig::fromArray($config);
    }

    public static function invalidTimeoutProvider(): array
    {
        return [
            [['requestTimeout' => 0]],
            [['requestTimeout' => -1]],
            [['requestTimeout' => 'eight']],
            [['managementRequestTimeout' => INF]],
            [['connectTimeout' => 0]],
        ];
    }

    public function testNumericStringsAreAcceptedForEnvironmentBasedConfiguration(): void
    {
        $config = HttpClientConfig::fromArray([
            'requestTimeout' => '8.5',
            'managementRequestTimeout' => '45',
            'connectTimeout' => '2',
        ]);

        $this->assertSame(8.5, $config->requestTimeout());
        $this->assertSame(45.0, $config->requestTimeout(true));
        $this->assertSame(2.0, $config->connectTimeout());
    }

    private function apiWithResponses(
        HttpClientConfig $httpClientConfig,
        array $responses,
        array &$requests
    ): API {
        $handlerStack = HandlerStack::create(new MockHandler($responses));
        $handlerStack->push(Middleware::history($requests));
        $client = new Client(['handler' => $handlerStack]);

        return new API('project', 'management-key', false, null, $httpClientConfig, $client);
    }

    private function retryableException(int $statusCode): RequestException
    {
        $request = new Request('GET', 'https://example.com/test');
        $response = new Response($statusCode, [], '');

        return new RequestException('transient error', $request, $response);
    }

    private function setRetryDelays(API $api, array $retryDelaysUs): void
    {
        $reflection = new ReflectionClass(API::class);
        $retryDelays = $reflection->getProperty('retryDelaysUs');
        $retryDelays->setAccessible(true);
        $retryDelays->setValue($api, $retryDelaysUs);
    }
}
