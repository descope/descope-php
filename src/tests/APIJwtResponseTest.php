<?php

namespace Descope\Tests;

use PHPUnit\Framework\TestCase;
use Descope\SDK\API;

final class APIJwtResponseTest extends TestCase
{
    private API $api;

    protected function setUp(): void
    {
        $this->api = new API('test_project_id', null);
    }

    public function testReadsClaimsFromSessionJwt(): void
    {
        $response = $this->api->generateJwtResponse([
            'sessionJwt' => $this->jwt([
                'iss' => 'https://api.descope.com/test_project_id',
                'sub' => 'user-1',
                'roles' => ['admin'],
                'permissions' => ['read'],
                'tenants' => ['T1' => ['roles' => ['member']]],
            ]),
            'refreshJwt' => $this->jwt(['sub' => 'user-1', 'roles' => ['ignored']]),
        ]);

        $this->assertSame(['admin'], $response['roles']);
        $this->assertSame(['read'], $response['permissions']);
        $this->assertSame(['T1' => ['roles' => ['member']]], $response['tenants']);
        $this->assertSame('test_project_id', $response['projectId']);
        $this->assertSame('user-1', $response['userId']);
    }

    public function testFallsBackToRefreshJwtClaims(): void
    {
        $response = $this->api->generateJwtResponse([
            'refreshJwt' => $this->jwt([
                'iss' => 'test_project_id',
                'sub' => 'user-2',
                'roles' => ['viewer'],
            ]),
        ]);

        $this->assertSame(['viewer'], $response['roles']);
        $this->assertSame([], $response['permissions']);
        $this->assertSame('test_project_id', $response['projectId']);
        $this->assertSame('user-2', $response['userId']);
    }

    public function testMalformedTokensYieldEmptyClaims(): void
    {
        $response = $this->api->generateJwtResponse(['sessionJwt' => 'not-a-jwt']);

        $this->assertSame([], $response['roles']);
        $this->assertSame([], $response['permissions']);
        $this->assertSame([], $response['tenants']);
        $this->assertSame('', $response['userId']);
    }

    public function testKeepsRawTokens(): void
    {
        $session = $this->jwt(['sub' => 'user-1']);
        $response = $this->api->generateJwtResponse(['sessionJwt' => $session, 'refreshJwt' => 'rjwt']);

        $this->assertSame($session, $response['sessionToken']);
        $this->assertSame('rjwt', $response['refreshSessionToken']);
    }

    private function jwt(array $claims): string
    {
        return $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])) . '.' .
            $this->base64UrlEncode(json_encode($claims)) . '.' .
            $this->base64UrlEncode('signature');
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
