<?php

namespace Descope\Tests;

use PHPUnit\Framework\TestCase;
use Descope\SDK\Token\DPoP;

/**
 * Unit tests for DPoP (RFC 9449) proof validation.
 *
 * These tests exercise the static helper methods directly without
 * requiring a network connection or live Descope credentials.
 */
final class DPoPTest extends TestCase
{
    // -------------------------------------------------------------------------
    // getThumbprint
    // -------------------------------------------------------------------------

    public function testGetThumbprintReturnsCnfJkt(): void
    {
        $claims = ['cnf' => ['jkt' => 'abc123thumbprint']];
        $this->assertSame('abc123thumbprint', DPoP::getThumbprint($claims));
    }

    public function testGetThumbprintReturnsEmptyWhenAbsent(): void
    {
        $this->assertSame('', DPoP::getThumbprint([]));
        $this->assertSame('', DPoP::getThumbprint(['cnf' => []]));
        $this->assertSame('', DPoP::getThumbprint(['sub' => 'user123']));
    }

    // -------------------------------------------------------------------------
    // validateProof: no-op when token is not DPoP-bound
    // -------------------------------------------------------------------------

    public function testValidateProofNoOpWhenNoCnfJkt(): void
    {
        // Build a minimal session JWT payload with no cnf claim
        $payload = base64_url_encode(json_encode(['sub' => 'user1', 'exp' => time() + 3600]));
        $fakeJwt = 'header.' . $payload . '.signature';

        // Should not throw — token is not DPoP-bound
        DPoP::validateProof('', 'GET', 'https://example.com/api', $fakeJwt);
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // validateProof: structural / header checks
    // -------------------------------------------------------------------------

    public function testValidateProofThrowsWhenProofTooLong(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/exceeds maximum length/');

        $sessionJwt = $this->buildSessionJwt(['cnf' => ['jkt' => 'somethumb']]);
        DPoP::validateProof(str_repeat('a', 8193), 'GET', 'https://example.com/', $sessionJwt);
    }

    public function testValidateProofThrowsWhenProofEmptyAndBound(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/DPoP proof required/');

        $sessionJwt = $this->buildSessionJwt(['cnf' => ['jkt' => 'somethumb']]);
        DPoP::validateProof('', 'GET', 'https://example.com/', $sessionJwt);
    }

    public function testValidateProofThrowsOnMalformedJwt(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/malformed DPoP JWT/');

        $sessionJwt = $this->buildSessionJwt(['cnf' => ['jkt' => 'somethumb']]);
        DPoP::validateProof('notajwt', 'GET', 'https://example.com/', $sessionJwt);
    }

    public function testValidateProofThrowsOnWrongTyp(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/typ must be dpop\+jwt/');

        $sessionJwt = $this->buildSessionJwt(['cnf' => ['jkt' => 'somethumb']]);
        $proof = $this->buildRawProof(['typ' => 'JWT', 'alg' => 'RS256'], []);
        DPoP::validateProof($proof, 'GET', 'https://example.com/', $sessionJwt);
    }

    public function testValidateProofThrowsOnRejectedAlg(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/rejected algorithm/');

        $sessionJwt = $this->buildSessionJwt(['cnf' => ['jkt' => 'somethumb']]);
        $proof = $this->buildRawProof(['typ' => 'dpop+jwt', 'alg' => 'HS256'], []);
        DPoP::validateProof($proof, 'GET', 'https://example.com/', $sessionJwt);
    }

    public function testValidateProofThrowsOnMissingJwk(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/missing jwk header/');

        $sessionJwt = $this->buildSessionJwt(['cnf' => ['jkt' => 'somethumb']]);
        $proof = $this->buildRawProof(['typ' => 'dpop+jwt', 'alg' => 'RS256'], []);
        DPoP::validateProof($proof, 'GET', 'https://example.com/', $sessionJwt);
    }

    public function testValidateProofThrowsOnSymmetricKey(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/symmetric key not allowed/');

        $sessionJwt = $this->buildSessionJwt(['cnf' => ['jkt' => 'somethumb']]);
        $proof = $this->buildRawProof(
            ['typ' => 'dpop+jwt', 'alg' => 'RS256', 'jwk' => ['kty' => 'oct', 'k' => 'abc']],
            []
        );
        DPoP::validateProof($proof, 'GET', 'https://example.com/', $sessionJwt);
    }

    public function testValidateProofThrowsOnPrivateKeyInJwk(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/must not contain a private key/');

        $sessionJwt = $this->buildSessionJwt(['cnf' => ['jkt' => 'somethumb']]);
        $proof = $this->buildRawProof(
            ['typ' => 'dpop+jwt', 'alg' => 'RS256', 'jwk' => ['kty' => 'RSA', 'n' => 'x', 'e' => 'AQAB', 'd' => 'secret']],
            []
        );
        DPoP::validateProof($proof, 'GET', 'https://example.com/', $sessionJwt);
    }

    // -------------------------------------------------------------------------
    // Full round-trip test with a real RSA key pair
    // -------------------------------------------------------------------------

    public function testValidateProofSucceedsWithValidRsaProof(): void
    {
        // Generate a fresh RSA key pair for this test
        $keyResource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $this->assertNotFalse($keyResource, 'Could not generate RSA key pair');

        $details = openssl_pkey_get_details($keyResource);
        $jwk = [
            'kty' => 'RSA',
            'n'   => rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '='),
            'e'   => rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '='),
        ];

        // Compute thumbprint
        $members = ['e' => $jwk['e'], 'kty' => 'RSA', 'n' => $jwk['n']];
        ksort($members);
        $thumbprint = rtrim(strtr(base64_encode(hash('sha256', json_encode($members, JSON_UNESCAPED_SLASHES), true)), '+/', '-_'), '=');

        // Build session JWT with cnf.jkt
        $sessionToken = $this->buildSessionJwt(['sub' => 'user1', 'cnf' => ['jkt' => $thumbprint]]);

        // Build DPoP proof
        $method = 'GET';
        $url = 'https://api.example.com/resource';
        $ath = rtrim(strtr(base64_encode(hash('sha256', $sessionToken, true)), '+/', '-_'), '=');

        $header = ['typ' => 'dpop+jwt', 'alg' => 'RS256', 'jwk' => $jwk];
        $payload = [
            'jti' => bin2hex(random_bytes(16)),
            'htm' => $method,
            'htu' => $url,
            'iat' => time(),
            'ath' => $ath,
        ];

        $headerEnc  = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $payloadEnc = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $signingInput = $headerEnc . '.' . $payloadEnc;

        openssl_sign($signingInput, $signature, $keyResource, OPENSSL_ALGO_SHA256);
        $sigEnc = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        $dpopProof = $signingInput . '.' . $sigEnc;

        // Should not throw
        DPoP::validateProof($dpopProof, $method, $url, $sessionToken);
        $this->assertTrue(true);
    }

    public function testValidateProofSucceedsWithValidEcProof(): void
    {
        // Generate a fresh EC P-256 key pair
        $keyResource = openssl_pkey_new([
            'curve_name'         => 'prime256v1',
            'private_key_type'   => OPENSSL_KEYTYPE_EC,
        ]);
        $this->assertNotFalse($keyResource, 'Could not generate EC key pair');

        $details = openssl_pkey_get_details($keyResource);
        $jwk = [
            'kty' => 'EC',
            'crv' => 'P-256',
            'x'   => rtrim(strtr(base64_encode(str_pad($details['ec']['x'], 32, "\x00", STR_PAD_LEFT)), '+/', '-_'), '='),
            'y'   => rtrim(strtr(base64_encode(str_pad($details['ec']['y'], 32, "\x00", STR_PAD_LEFT)), '+/', '-_'), '='),
        ];

        // Compute thumbprint
        $members = ['crv' => $jwk['crv'], 'kty' => 'EC', 'x' => $jwk['x'], 'y' => $jwk['y']];
        ksort($members);
        $thumbprint = rtrim(strtr(base64_encode(hash('sha256', json_encode($members, JSON_UNESCAPED_SLASHES), true)), '+/', '-_'), '=');

        $sessionToken = $this->buildSessionJwt(['sub' => 'user1', 'cnf' => ['jkt' => $thumbprint]]);

        $method = 'POST';
        $url = 'https://api.example.com/submit';
        $ath = rtrim(strtr(base64_encode(hash('sha256', $sessionToken, true)), '+/', '-_'), '=');

        $header  = ['typ' => 'dpop+jwt', 'alg' => 'ES256', 'jwk' => $jwk];
        $payload = [
            'jti' => bin2hex(random_bytes(16)),
            'htm' => $method,
            'htu' => $url,
            'iat' => time(),
            'ath' => $ath,
        ];

        $headerEnc  = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $payloadEnc = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $signingInput = $headerEnc . '.' . $payloadEnc;

        openssl_sign($signingInput, $derSig, $keyResource, OPENSSL_ALGO_SHA256);

        // Convert DER to raw R||S for JWT
        $rawSig = $this->derEcSigToRaw($derSig, 32);
        $sigEnc = rtrim(strtr(base64_encode($rawSig), '+/', '-_'), '=');

        $dpopProof = $signingInput . '.' . $sigEnc;

        DPoP::validateProof($dpopProof, $method, $url, $sessionToken);
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // Payload claim checks (htm, htu, iat, ath) — tested post-signature so we
    // use a real RSA key pair to produce a structurally valid (but claim-invalid) proof.
    // -------------------------------------------------------------------------

    public function testValidateProofThrowsOnHtmMismatch(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/htm mismatch/');

        [$dpopProof, $sessionToken] = $this->buildSignedProof('POST', 'https://example.com/api');
        DPoP::validateProof($dpopProof, 'GET', 'https://example.com/api', $sessionToken);
    }

    public function testValidateProofThrowsOnHtuMismatch(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/htu does not match/');

        [$dpopProof, $sessionToken] = $this->buildSignedProof('GET', 'https://example.com/api');
        DPoP::validateProof($dpopProof, 'GET', 'https://example.com/other', $sessionToken);
    }

    public function testValidateProofThrowsOnExpiredIat(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/iat out of acceptable window/');

        [$dpopProof, $sessionToken] = $this->buildSignedProof('GET', 'https://example.com/api', time() - 120);
        DPoP::validateProof($dpopProof, 'GET', 'https://example.com/api', $sessionToken);
    }

    public function testValidateProofThrowsOnFutureIat(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/iat out of acceptable window/');

        [$dpopProof, $sessionToken] = $this->buildSignedProof('GET', 'https://example.com/api', time() + 60);
        DPoP::validateProof($dpopProof, 'GET', 'https://example.com/api', $sessionToken);
    }

    public function testValidateProofThrowsOnAthMismatch(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/ath does not match/');

        // Build proof with a different session token hash
        [$dpopProof, $sessionToken] = $this->buildSignedProof('GET', 'https://example.com/api', null, 'wrong-token');
        DPoP::validateProof($dpopProof, 'GET', 'https://example.com/api', $sessionToken);
    }

    // -------------------------------------------------------------------------
    // htu URL normalisation edge cases
    // -------------------------------------------------------------------------

    public function testHtuMatchesIgnoresDefaultHttpsPort(): void
    {
        [$dpopProof, $sessionToken] = $this->buildSignedProof('GET', 'https://example.com:443/api');
        // Should not throw — 443 is default for https
        DPoP::validateProof($dpopProof, 'GET', 'https://example.com/api', $sessionToken);
        $this->assertTrue(true);
    }

    public function testHtuMatchesIgnoresDefaultHttpPort(): void
    {
        [$dpopProof, $sessionToken] = $this->buildSignedProof('GET', 'http://example.com:80/api');
        DPoP::validateProof($dpopProof, 'GET', 'http://example.com/api', $sessionToken);
        $this->assertTrue(true);
    }

    public function testHtuMatchesIsCaseInsensitiveForSchemeAndHost(): void
    {
        [$dpopProof, $sessionToken] = $this->buildSignedProof('GET', 'HTTPS://EXAMPLE.COM/api');
        DPoP::validateProof($dpopProof, 'GET', 'https://example.com/api', $sessionToken);
        $this->assertTrue(true);
    }

    public function testHtuMismatchOnNonDefaultPort(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/htu does not match/');

        [$dpopProof, $sessionToken] = $this->buildSignedProof('GET', 'https://example.com:8443/api');
        DPoP::validateProof($dpopProof, 'GET', 'https://example.com/api', $sessionToken);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build a fake (unsigned) session JWT with given payload claims.
     */
    private function buildSessionJwt(array $claims): string
    {
        $header  = base64_url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_url_encode(json_encode(array_merge(['exp' => time() + 3600], $claims)));
        return $header . '.' . $payload . '.fakesig';
    }

    /**
     * Build a raw (unsigned) DPoP JWT string from separate header/payload arrays.
     * Signature is a placeholder.
     */
    private function buildRawProof(array $header, array $payload): string
    {
        $h = base64_url_encode(json_encode($header));
        $p = base64_url_encode(json_encode($payload));
        return $h . '.' . $p . '.fakesig';
    }

    /**
     * Build a properly signed RSA DPoP proof + matching session token.
     *
     * @param  string   $method
     * @param  string   $url
     * @param  int|null $iat     Override iat timestamp.
     * @param  string   $athToken The token to hash for ath (defaults to session token).
     * @return array{string, string} [$dpopProof, $sessionToken]
     */
    private function buildSignedProof(
        string $method,
        string $url,
        ?int $iat = null,
        ?string $athToken = null
    ): array {
        $keyResource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        $details = openssl_pkey_get_details($keyResource);
        $jwk = [
            'kty' => 'RSA',
            'n'   => rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '='),
            'e'   => rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '='),
        ];

        $members = ['e' => $jwk['e'], 'kty' => 'RSA', 'n' => $jwk['n']];
        ksort($members);
        $thumbprint = rtrim(strtr(base64_encode(hash('sha256', json_encode($members, JSON_UNESCAPED_SLASHES), true)), '+/', '-_'), '=');

        $sessionToken = $this->buildSessionJwt(['sub' => 'u1', 'cnf' => ['jkt' => $thumbprint]]);
        $hashSource   = $athToken ?? $sessionToken;
        $ath = rtrim(strtr(base64_encode(hash('sha256', $hashSource, true)), '+/', '-_'), '=');

        $header  = ['typ' => 'dpop+jwt', 'alg' => 'RS256', 'jwk' => $jwk];
        $payload = [
            'jti' => bin2hex(random_bytes(8)),
            'htm' => strtoupper($method),
            'htu' => $url,
            'iat' => $iat ?? time(),
            'ath' => $ath,
        ];

        $headerEnc  = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $payloadEnc = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $signingInput = $headerEnc . '.' . $payloadEnc;

        openssl_sign($signingInput, $signature, $keyResource, OPENSSL_ALGO_SHA256);
        $sigEnc = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return [$signingInput . '.' . $sigEnc, $sessionToken];
    }

    /**
     * Convert a DER-encoded ECDSA signature to raw R||S bytes.
     */
    private function derEcSigToRaw(string $der, int $coordLen): string
    {
        // DER: SEQUENCE { INTEGER r, INTEGER s }
        $pos = 2; // skip SEQUENCE tag + length
        if (ord($der[1]) > 0x7f) {
            $pos += ord($der[1]) & 0x7f;
        }

        $readInt = function (string $der, int &$pos) use ($coordLen): string {
            $pos++; // skip INTEGER tag (0x02)
            $len = ord($der[$pos++]);
            $val = substr($der, $pos, $len);
            $pos += $len;
            // Strip leading zero if present (added for positive encoding)
            if (strlen($val) > $coordLen && ord($val[0]) === 0x00) {
                $val = substr($val, 1);
            }
            return str_pad($val, $coordLen, "\x00", STR_PAD_LEFT);
        };

        $r = $readInt($der, $pos);
        $s = $readInt($der, $pos);
        return $r . $s;
    }
}

/**
 * Standalone base64url-encode helper for tests (avoids depending on DPoP internals).
 */
function base64_url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
