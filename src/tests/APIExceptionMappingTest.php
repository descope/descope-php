<?php

namespace Descope\Tests;

use Descope\SDK\API;
use Descope\SDK\Exception\AuthException;
use Descope\SDK\Exception\RateLimitException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class APIExceptionMappingTest extends TestCase
{
    private function apiWithMockedClient(MockHandler $mockHandler): API
    {
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handlerStack]);

        $api = new API('project', null, false);
        $reflection = new ReflectionClass($api);
        $httpClientProp = $reflection->getProperty('httpClient');
        $httpClientProp->setAccessible(true);
        $httpClientProp->setValue($api, $client);

        return $api;
    }

    public function testMapsHttp429ToRateLimitExceptionAndParsesErrorFields(): void
    {
        $body = json_encode([
            'errorCode' => 'rate_limit',
            'errorDescription' => 'too many requests',
        ]);

        $request = new Request('GET', 'https://example.com/test');
        $response = new Response(429, [], $body);
        $requestException = new RequestException('Request failed', $request, $response);

        $api = $this->apiWithMockedClient(new MockHandler([$requestException]));

        try {
            $api->doGet('https://example.com/test', false);
            $this->fail('Expected RateLimitException to be thrown');
        } catch (RateLimitException $e) {
            $this->assertSame('too many requests', $e->getMessage());
            $this->assertStringContainsString('"errorType":"rate_limit"', (string) $e);
            $this->assertInstanceOf(RequestException::class, $e->getPrevious());
        }
    }

    public function testMapsOtherHttpErrorsToAuthExceptionAndParsesErrorFields(): void
    {
        $body = json_encode([
            'errorCode' => 'bad_request',
            'errorDescription' => 'invalid input',
        ]);

        $request = new Request('POST', 'https://example.com/test');
        $response = new Response(400, [], $body);
        $requestException = new RequestException('Request failed', $request, $response);

        $api = $this->apiWithMockedClient(new MockHandler([$requestException]));

        try {
            $api->doPost('https://example.com/test', [], false);
            $this->fail('Expected AuthException to be thrown');
        } catch (AuthException $e) {
            $this->assertSame('invalid input', $e->getMessage());
            $this->assertStringContainsString('"errorType":"bad_request"', (string) $e);
            $this->assertInstanceOf(RequestException::class, $e->getPrevious());
        }
    }
}

