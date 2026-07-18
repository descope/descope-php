<?php

namespace Descope\Tests;

use Descope\SDK\API;
use Descope\SDK\Configuration\SDKConfig;
use Descope\SDK\EndpointsV1;
use Descope\SDK\Management\MgmtV1;
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

    public function testStaticAuthEndpointCannotRedirectProjectIdRequest(): void
    {
        $requests = [];
        $api = new API('project', null, false, 'https://trusted.example');
        $client = $this->clientCapturingRequests($requests, json_encode(['ok' => true]));

        $reflection = new ReflectionClass(API::class);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($api, $client);

        EndpointsV1::setBaseUrlFromString('https://attacker.example.com');
        $api->doPost(EndpointsV1::$SIGN_IN_PASSWORD_PATH, ['loginId' => 'user@example.com', 'password' => 'secret'], false);

        $this->assertCount(1, $requests);
        $this->assertSame('https://trusted.example/v1/auth/password/signin', (string) $requests[0]['request']->getUri());
        $this->assertSame(
            ['loginId' => 'user@example.com', 'password' => 'secret'],
            json_decode((string) $requests[0]['request']->getBody(), true)
        );
    }

    public function testStaticManagementEndpointCannotRedirectCredentialedRequest(): void
    {
        $requests = [];
        $api = new API('project', 'mgmt-key', false, 'https://trusted.example');
        $client = $this->clientCapturingRequests($requests, json_encode(['ok' => true]));

        $reflection = new ReflectionClass(API::class);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($api, $client);

        MgmtV1::setBaseUrlFromString('https://attacker.example.com');
        $api->doDelete(MgmtV1::$USER_DELETE_PATH);

        $this->assertCount(1, $requests);
        $this->assertSame('https://trusted.example/v1/mgmt/user/delete', (string) $requests[0]['request']->getUri());
        $this->assertSame('Bearer project:mgmt-key', $requests[0]['request']->getHeaderLine('Authorization'));
    }
}
