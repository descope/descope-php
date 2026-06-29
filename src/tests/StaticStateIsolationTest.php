<?php

namespace Descope\Tests;

use Descope\SDK\API;
use Descope\SDK\Configuration\SDKConfig;
use Descope\SDK\Exception\AuthException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class StaticStateIsolationTest extends TestCase
{
    /** @var array<int,\Psr\Http\Message\RequestInterface> */
    private function clientCapturingRequests(array &$container, string $body): Client
    {
        $mock = new MockHandler([new Response(200, [], $body), new Response(200, [], $body)]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($container));
        return new Client(['handler' => $stack]);
    }

    public function testJWKSHostIsPerInstanceAndNotHijackedByLaterInstance(): void
    {
        $jwks = json_encode(['keys' => [['kid' => 'k1']]]);

        $containerA = [];
        $configA = new SDKConfig([
            'projectId' => 'projectA',
            'baseUrl' => 'https://api.descope.com',
        ]);
        $configA->client = $this->clientCapturingRequests($containerA, $jwks);

        // A later instance points at an attacker-controlled host.
        $configB = new SDKConfig([
            'projectId' => 'projectB',
            'baseUrl' => 'https://attacker.example.com',
        ]);
        $containerB = [];
        $configB->client = $this->clientCapturingRequests($containerB, $jwks);

        // A fetches its JWKS AFTER B was constructed.
        $configA->getJWKSets(true);

        $this->assertCount(1, $containerA);
        $requestedUri = (string) $containerA[0]['request']->getUri();
        $this->assertSame('https://api.descope.com/v2/keys/projectA', $requestedUri);
        $this->assertStringNotContainsString('attacker.example.com', $requestedUri);
    }

    public function testCredentialedRequestToForeignHostIsRefused(): void
    {
        $api = new API('project', 'mgmt-key', false, 'https://api.descope.com');

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Refusing to send credentials to unexpected host');

        // A hijacked management path would resolve to a different host.
        $api->doDelete('https://attacker.example.com/v1/mgmt/user/delete');
    }

    public function testProjectIdOnlyRequestToForeignHostIsAllowed(): void
    {
        // The bare project ID is public, so requests carrying only it are not host-restricted.
        $api = new API('project', null, false, 'https://api.descope.com');

        $reflection = new ReflectionClass(API::class);
        $mock = new MockHandler([new Response(200, [], json_encode(['ok' => true]))]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $prop = $reflection->getProperty('httpClient');
        $prop->setAccessible(true);
        $prop->setValue($api, $client);

        $result = $api->doGet('https://example.com/test', false);
        $this->assertSame(['ok' => true], $result);
    }
}
