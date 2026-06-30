<?php

namespace Descope\Tests;

use PHPUnit\Framework\TestCase;
use Descope\SDK\Token\Extractor;
use Descope\SDK\Configuration\SDKConfig;
use Descope\SDK\Exception\TokenException;
use Descope\SDK\EndpointsV1;
use Descope\SDK\EndpointsV2;

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
        // Reference EndpointsV1 first so EndpointsV1.php is autoloaded (it also
        // defines EndpointsV2, which has no file of its own), then both classes
        // are available when SDKConfig resolves the JWKS URL.
        EndpointsV1::setBaseUrlFromString('https://api.descope.com');
        EndpointsV2::setBaseUrlFromString('https://api.descope.com');

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
        // attacker-controlled payload. JWKS resolution failures are surfaced
        // as TokenException so validateJWT()'s key-refresh retry engages and
        // the error never propagates as an uncaught generic exception.
        $this->expectException(TokenException::class);
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
