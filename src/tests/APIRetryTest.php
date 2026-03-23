<?php

namespace Descope\Tests;

use Descope\SDK\API;
use Descope\SDK\Exception\AuthException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class APIRetryTest extends TestCase
{
    private function apiWithMockedClient(MockHandler $mockHandler): API
    {
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handlerStack]);

        $api = new API('project', null, false);
        $reflection = new ReflectionClass(API::class);

        $httpClientProp = $reflection->getProperty('httpClient');
        $httpClientProp->setAccessible(true);
        $httpClientProp->setValue($api, $client);

        $retryDelaysProp = $reflection->getProperty('retryDelaysUs');
        $retryDelaysProp->setAccessible(true);
        $retryDelaysProp->setValue($api, [0, 0, 0]);

        return $api;
    }

    private function retryableException(int $statusCode): RequestException
    {
        $request = new Request('GET', 'https://example.com/test');
        $response = new Response($statusCode, [], '');
        return new RequestException('transient error', $request, $response);
    }

    private function successResponse(): Response
    {
        return new Response(200, [], json_encode(['ok' => true]));
    }

    /**
     * @dataProvider retryableStatusCodeProvider
     */
    public function testRetriesOnRetryableStatusCodeAndSucceedsOnSecondAttempt(int $statusCode): void
    {
        $api = $this->apiWithMockedClient(new MockHandler([
            $this->retryableException($statusCode),
            $this->successResponse(),
        ]));

        $result = $api->doGet('https://example.com/test', false);
        $this->assertSame(['ok' => true], $result);
    }

    public static function retryableStatusCodeProvider(): array
    {
        return [[503], [521], [522], [524], [530]];
    }

    public function testRetriesUpToThreeTimesAndThrowsOnExhaustion(): void
    {
        $api = $this->apiWithMockedClient(new MockHandler([
            $this->retryableException(503),
            $this->retryableException(503),
            $this->retryableException(503),
            $this->retryableException(503),
        ]));

        $this->expectException(AuthException::class);
        $api->doGet('https://example.com/test', false);
    }

    public function testDoesNotRetryOnNonRetryableStatusCodes(): void
    {
        foreach ([400, 401, 403, 404, 500, 502] as $statusCode) {
            $request = new Request('GET', 'https://example.com/test');
            $response = new Response($statusCode, [], '');
            $exception = new RequestException('error', $request, $response);

            $api = $this->apiWithMockedClient(new MockHandler([$exception]));

            try {
                $api->doGet('https://example.com/test', false);
                $this->fail("Expected exception for status $statusCode");
            } catch (AuthException $e) {
                $this->assertStringContainsString((string) $statusCode, (string) $e);
            }
        }
    }

    public function testRetryWorksForDoPost(): void
    {
        $api = $this->apiWithMockedClient(new MockHandler([
            $this->retryableException(503),
            $this->successResponse(),
        ]));

        $result = $api->doPost('https://example.com/test', []);
        $this->assertSame(['ok' => true], $result);
    }

    public function testRetryWorksForDoDelete(): void
    {
        $api = $this->apiWithMockedClient(new MockHandler([
            $this->retryableException(503),
            $this->successResponse(),
        ]));

        $result = $api->doDelete('https://example.com/test');
        $this->assertSame(['ok' => true], $result);
    }

    public function testSucceedsImmediatelyWithNoRetry(): void
    {
        $api = $this->apiWithMockedClient(new MockHandler([
            $this->successResponse(),
        ]));

        $result = $api->doGet('https://example.com/test', false);
        $this->assertSame(['ok' => true], $result);
    }

    public function testSucceedsOnThirdRetry(): void
    {
        $api = $this->apiWithMockedClient(new MockHandler([
            $this->retryableException(503),
            $this->retryableException(522),
            $this->retryableException(530),
            $this->successResponse(),
        ]));

        $result = $api->doGet('https://example.com/test', false);
        $this->assertSame(['ok' => true], $result);
    }
}
