<?php

namespace Descope\Tests;

use PHPUnit\Framework\TestCase;
use Descope\SDK\Token\Extractor;
use Descope\SDK\Configuration\SDKConfig;
use Descope\SDK\Exception\TokenException;

/**
 * Regression tests for CWE-347: getClaims() must not return unverified claims.
 *
 * Prior to this fix, Extractor::getClaims() returned the JWT payload after only
 * checking the `alg` header, so a forged token with arbitrary roles/permissions
 * and a garbage signature was returned intact. getClaims() must now verify the
 * signature (and expiration) before returning claims, and the unverified decode
 * path must be reachable only via the explicitly-named getClaimsUnverified().
 */
final class ExtractorClaimsTest extends TestCase
{
    private Extractor $extractor;

    protected function setUp(): void
    {
        $config = new SDKConfig([
            'projectId' => 'test_project_id',
        ]);
        $this->extractor = new Extractor($config);
    }

    /**
     * Build a well-formed but UNSIGNED RS256 JWT with attacker-chosen claims.
     */
    private function forgeToken(array $claims): string
    {
        $encode = static function (array $data): string {
            return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
        };

        $header = $encode(['alg' => 'RS256', 'typ' => 'JWT', 'kid' => 'forged-kid']);
        $payload = $encode($claims);
        $signature = rtrim(strtr(base64_encode('not-a-real-signature'), '+/', '-_'), '=');

        return "$header.$payload.$signature";
    }

    public function testGetClaimsNeverReturnsForgedClaims(): void
    {
        $forged = $this->forgeToken([
            'sub' => 'attacker',
            'roles' => ['admin'],
            'permissions' => ['*'],
        ]);

        // getClaims() must fail closed: it must throw rather than return the
        // attacker-controlled payload. The exact throwable depends on whether
        // JWKS resolution can reach the network, but it must never succeed.
        $this->expectException(\Throwable::class);
        $this->extractor->getClaims($forged);
    }

    public function testGetClaimsUnverifiedReturnsPayloadWithoutTrust(): void
    {
        $claims = [
            'sub' => 'user-123',
            'roles' => ['viewer'],
        ];
        $token = $this->forgeToken($claims);

        // The unverified path is explicitly opt-in and does not touch the
        // network or signature; it simply decodes the payload.
        $decoded = $this->extractor->getClaimsUnverified($token);

        $this->assertSame('user-123', $decoded['sub']);
        $this->assertSame(['viewer'], $decoded['roles']);
    }

    public function testGetClaimsRejectsMalformedToken(): void
    {
        $this->expectException(TokenException::class);
        $this->extractor->getClaims('not-a-jwt');
    }

    public function testGetClaimsRejectsUnsupportedAlgorithm(): void
    {
        $encode = static function (array $data): string {
            return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
        };
        $token = $encode(['alg' => 'none']) . '.' . $encode(['sub' => 'x']) . '.';

        $this->expectException(TokenException::class);
        $this->extractor->getClaims($token);
    }
}
