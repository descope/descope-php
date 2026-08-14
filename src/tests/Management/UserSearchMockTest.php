<?php

namespace Descope\Tests\Management;

use PHPUnit\Framework\TestCase;
use Descope\SDK\DescopeSDK;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

final class UserSearchMockTest extends TestCase
{
    public function testSearchAllUsersSendsTimeFilterParams()
    {
        $requests = [];
        $mock = new MockHandler([new Response(200, [], json_encode(['users' => []]))]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($requests));
        $injectedClient = new Client(['handler' => $stack]);

        $sdk = new DescopeSDK([
            'projectId' => 'test_project_id',
            'managementKey' => 'test_management_key',
            'httpClient' => $injectedClient,
        ]);

        $fromCreatedTime = 1700000000000;
        $toCreatedTime = 1800000000000;
        $fromModifiedTime = 1700000000000;
        $toModifiedTime = 1800000000000;

        $sdk->management->user->searchAll(
            null,
            null,
            null,
            0,
            null,
            0,
            false,
            false,
            false,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $fromCreatedTime,
            $toCreatedTime,
            $fromModifiedTime,
            $toModifiedTime
        );

        $this->assertCount(1, $requests);
        $body = json_decode((string) $requests[0]['request']->getBody(), true);

        $this->assertSame($fromCreatedTime, $body['fromCreatedTime']);
        $this->assertSame($toCreatedTime, $body['toCreatedTime']);
        $this->assertSame($fromModifiedTime, $body['fromModifiedTime']);
        $this->assertSame($toModifiedTime, $body['toModifiedTime']);
    }

    public function testSearchAllTestUsersSendsTimeFilterParams()
    {
        $requests = [];
        $mock = new MockHandler([new Response(200, [], json_encode(['users' => []]))]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($requests));
        $injectedClient = new Client(['handler' => $stack]);

        $sdk = new DescopeSDK([
            'projectId' => 'test_project_id',
            'managementKey' => 'test_management_key',
            'httpClient' => $injectedClient,
        ]);

        $fromCreatedTime = 1700000000000;
        $toCreatedTime = 1800000000000;
        $fromModifiedTime = 1700000000000;
        $toModifiedTime = 1800000000000;

        $sdk->management->user->searchAllTestUsers(
            null,
            null,
            null,
            0,
            null,
            0,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $fromCreatedTime,
            $toCreatedTime,
            $fromModifiedTime,
            $toModifiedTime
        );

        $this->assertCount(1, $requests);
        $body = json_decode((string) $requests[0]['request']->getBody(), true);

        $this->assertSame($fromCreatedTime, $body['fromCreatedTime']);
        $this->assertSame($toCreatedTime, $body['toCreatedTime']);
        $this->assertSame($fromModifiedTime, $body['fromModifiedTime']);
        $this->assertSame($toModifiedTime, $body['toModifiedTime']);
    }
}
