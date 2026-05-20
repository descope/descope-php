<?php

namespace Descope\SDK\Token;

/**
 * DPoP (Demonstrated Proof of Possession) validation per RFC 9449.
 *
 * Validates DPoP proof JWTs when a session token is DPoP-bound (cnf.jkt present).
 */
class DPoP
{
    private const ALLOWED_ALGS = [
        'RS256', 'RS384', 'RS512',
        'ES256', 'ES384', 'ES512',
        'PS256', 'PS384', 'PS512',
        'EdDSA',
    ];

    private const MAX_PROOF_LEN = 8192;
    private const IAT_BACKWARD_WINDOW = 60;
    private const IAT_FORWARD_WINDOW = 5;

    /**
     * Returns the cnf.jkt thumbprint from decoded JWT claims, or empty string if not present.
     *
     * @param  array $claims Decoded JWT payload claims.
     * @return string JWK thumbprint or empty string.
     */
    public static function getThumbprint(array $claims): string
    {
        return $claims['cnf']['jkt'] ?? '';
    }

    /**
     * Validates a DPoP proof JWT per RFC 9449 §7.1-7.2.
     *
     * Does nothing if the session token has no cnf.jkt (token is not DPoP-bound).
     * Throws \Exception if validation fails.
     *
     * @param  string $dpopProof    The value of the DPoP HTTP header.
     * @param  string $method       The HTTP method of the request (e.g. "GET", "POST").
     * @param  string $requestUrl   The full URL of the request.
     * @param  string $sessionToken The raw session JWT string.
     * @throws \Exception if the DPoP proof is invalid.
     */
    public static function validateProof(
        string $dpopProof,
        string $method,
        string $requestUrl,
        string $sessionToken
    ): void {
        // Decode session token claims to check for cnf.jkt
        $claims = self::decodeTokenClaims($sessionToken);
        $storedJkt = self::getThumbprint($claims);

        // If the token is not DPoP-bound, nothing to validate
        if ($storedJkt === '') {
            return;
        }

        // Step 1-2: Check proof length
        $dpopProof = trim($dpopProof);
        if (strlen($dpopProof) > self::MAX_PROOF_LEN) {
            throw new \Exception('DPoP proof exceeds maximum length');
        }

        // Step 3: Require proof when token is DPoP-bound
        if ($dpopProof === '') {
            throw new \Exception('DPoP proof required: access token is DPoP-bound (cnf.jkt present)');
        }

        // Step 4-5: Split JWT parts
        $parts = explode('.', $dpopProof);
        if (count($parts) !== 3) {
            throw new \Exception('malformed DPoP JWT');
        }

        // Step 6: Decode header
        $header = self::base64urlDecodeJson($parts[0]);

        // Step 7: Verify typ
        if (($header['typ'] ?? '') !== 'dpop+jwt') {
            throw new \Exception('typ must be dpop+jwt');
        }

        // Steps 8-9: Verify alg
        $alg = $header['alg'] ?? '';
        if (!in_array($alg, self::ALLOWED_ALGS, true)) {
            throw new \Exception('rejected algorithm: ' . $alg);
        }

        // Steps 10-13: Validate JWK header
        $jwk = $header['jwk'] ?? null;
        if (empty($jwk) || !is_array($jwk)) {
            throw new \Exception('missing jwk header');
        }
        if (($jwk['kty'] ?? '') === 'oct') {
            throw new \Exception('symmetric key not allowed');
        }
        if (isset($jwk['d'])) {
            throw new \Exception('jwk must not contain a private key');
        }

        // Step 14-15: Import JWK and verify signature
        $publicKey = self::importJwkAsPublicKey($jwk, $alg);
        $signingInput = $parts[0] . '.' . $parts[1];
        $signature = self::base64urlDecode($parts[2]);
        self::verifyDpopSignature($signingInput, $signature, $publicKey, $jwk, $alg);

        // Step 16: Decode payload
        $payload = self::base64urlDecodeJson($parts[1]);

        // Steps 17-19: Validate required claims
        if (empty($payload['jti'])) {
            throw new \Exception('missing jti');
        }
        if (empty($payload['htm'])) {
            throw new \Exception('missing htm');
        }
        if (empty($payload['htu'])) {
            throw new \Exception('missing htu');
        }

        // Step 20: Validate htm (HTTP method)
        if ($payload['htm'] !== strtoupper($method)) {
            throw new \Exception('htm mismatch: expected ' . strtoupper($method) . ', got ' . $payload['htm']);
        }

        // Step 21: Validate htu (HTTP URI)
        if (!self::htuMatches($payload['htu'], $requestUrl)) {
            throw new \Exception('htu does not match request URL');
        }

        // Steps 22-24: Validate iat (issued at)
        if (!isset($payload['iat']) || !is_int($payload['iat'])) {
            throw new \Exception('missing or invalid iat');
        }
        $diff = time() - $payload['iat'];
        if ($diff <= -(self::IAT_FORWARD_WINDOW) || $diff >= self::IAT_BACKWARD_WINDOW) {
            throw new \Exception('iat out of acceptable window');
        }

        // Steps 25-30: Validate ath (access token hash)
        $ath = $payload['ath'] ?? '';
        if (empty($ath)) {
            throw new \Exception('missing ath claim');
        }
        $expectedAth = self::base64urlEncode(hash('sha256', $sessionToken, true));
        if (!hash_equals($expectedAth, $ath)) {
            throw new \Exception('ath does not match');
        }

        // Steps 31-33: Validate JWK thumbprint matches cnf.jkt
        $thumbprint = self::computeJwkThumbprint($jwk);
        if (!hash_equals($storedJkt, $thumbprint)) {
            throw new \Exception('DPoP proof key does not match cnf.jkt');
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Decode the payload of a JWT without signature verification.
     */
    private static function decodeTokenClaims(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new \Exception('Invalid session token format');
        }
        $payload = self::base64urlDecodeJson($parts[1]);
        return $payload;
    }

    /**
     * Base64url-decode then JSON-decode a JWT part.
     *
     * @throws \Exception on decode or parse failure.
     */
    private static function base64urlDecodeJson(string $data): array
    {
        $decoded = self::base64urlDecode($data);
        $result = json_decode($decoded, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON in JWT part: ' . json_last_error_msg());
        }
        return $result;
    }

    /**
     * Base64url-decode a string.
     */
    private static function base64urlDecode(string $data): string
    {
        $padded = str_pad(
            strtr($data, '-_', '+/'),
            strlen($data) + (4 - strlen($data) % 4) % 4,
            '='
        );
        $decoded = base64_decode($padded, true);
        if ($decoded === false) {
            throw new \Exception('Invalid base64url encoding');
        }
        return $decoded;
    }

    /**
     * Base64url-encode a string (no padding).
     */
    private static function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Import a JWK as an OpenSSL public key resource.
     *
     * @throws \Exception on unsupported or invalid key.
     * @return resource|\OpenSSLAsymmetricKey
     */
    private static function importJwkAsPublicKey(array $jwk, string $alg)
    {
        $kty = $jwk['kty'] ?? '';
        switch ($kty) {
            case 'RSA':
                return self::importRsaJwk($jwk);
            case 'EC':
                return self::importEcJwk($jwk);
            case 'OKP':
                return self::importOkpJwk($jwk);
            default:
                throw new \Exception('Unsupported JWK key type: ' . $kty);
        }
    }

    /**
     * Import an RSA JWK as an OpenSSL public key (PEM via DER).
     */
    private static function importRsaJwk(array $jwk)
    {
        if (empty($jwk['n']) || empty($jwk['e'])) {
            throw new \Exception('RSA JWK missing n or e parameters');
        }

        $modulus = self::base64urlDecode($jwk['n']);
        $exponent = self::base64urlDecode($jwk['e']);

        // Remove leading null bytes from modulus
        $modulus = ltrim($modulus, "\x00");

        // Build RSA public key DER (same approach as Extractor.php)
        $modBytes = pack('Ca*a*', 0x02, self::derEncodeLength(strlen($modulus)), $modulus);
        $expBytes = pack('Ca*a*', 0x02, self::derEncodeLength(strlen($exponent)), $exponent);
        $rsaSeq = pack('Ca*a*', 0x30, self::derEncodeLength(strlen($modBytes . $expBytes)), $modBytes . $expBytes);

        $algId = pack('H*', '300d06092a864886f70d0101010500');
        $bitStr = pack('Ca*', 0x03, self::derEncodeLength(strlen($rsaSeq) + 1) . "\x00" . $rsaSeq);
        $der = pack('Ca*a*', 0x30, self::derEncodeLength(strlen($algId . $bitStr)), $algId . $bitStr);

        $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n";
        $key = openssl_pkey_get_public($pem);
        if ($key === false) {
            throw new \Exception('Failed to import RSA JWK as public key');
        }
        return $key;
    }

    /**
     * Import an EC JWK as an OpenSSL public key (DER-encoded SubjectPublicKeyInfo).
     *
     * Supports P-256 (ES256), P-384 (ES384), P-521 (ES512).
     */
    private static function importEcJwk(array $jwk)
    {
        $crv = $jwk['crv'] ?? '';
        if (empty($jwk['x']) || empty($jwk['y'])) {
            throw new \Exception('EC JWK missing x or y parameters');
        }

        $x = self::base64urlDecode($jwk['x']);
        $y = self::base64urlDecode($jwk['y']);

        // EC OIDs and expected coordinate byte lengths
        $curveParams = [
            'P-256' => ['oid' => '2a8648ce3d030107', 'len' => 32],
            'P-384' => ['oid' => '2b81040022',       'len' => 48],
            'P-521' => ['oid' => '2b81040023',       'len' => 66],
        ];

        if (!isset($curveParams[$crv])) {
            throw new \Exception('Unsupported EC curve: ' . $crv);
        }

        $coordLen = $curveParams[$crv]['len'];
        // Pad x and y to expected length
        $x = str_pad($x, $coordLen, "\x00", STR_PAD_LEFT);
        $y = str_pad($y, $coordLen, "\x00", STR_PAD_LEFT);

        // Uncompressed EC point: 0x04 || x || y
        $point = "\x04" . $x . $y;
        $bitString = "\x00" . $point;

        // Build SubjectPublicKeyInfo DER:
        // SEQUENCE {
        //   SEQUENCE { OID ecPublicKey, OID curve }
        //   BIT STRING { 0x04 || x || y }
        // }
        $ecPublicKeyOid = pack('H*', '2a8648ce3d0201'); // OID 1.2.840.10045.2.1 (ecPublicKey)
        $curveOid = pack('H*', $curveParams[$crv]['oid']);

        $ecOidDer = pack('Ca*', 0x06, self::derEncodeLength(strlen($ecPublicKeyOid)) . $ecPublicKeyOid);
        $curveOidDer = pack('Ca*', 0x06, self::derEncodeLength(strlen($curveOid)) . $curveOid);
        $algSeq = pack('Ca*a*', 0x30, self::derEncodeLength(strlen($ecOidDer . $curveOidDer)), $ecOidDer . $curveOidDer);

        $bitStr = pack('Ca*a*', 0x03, self::derEncodeLength(strlen($bitString)), $bitString);
        $spki = pack('Ca*a*', 0x30, self::derEncodeLength(strlen($algSeq . $bitStr)), $algSeq . $bitStr);

        $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($spki), 64, "\n") . "-----END PUBLIC KEY-----\n";
        $key = openssl_pkey_get_public($pem);
        if ($key === false) {
            throw new \Exception('Failed to import EC JWK as public key: ' . openssl_error_string());
        }
        return $key;
    }

    /**
     * Import an OKP (Ed25519) JWK as an OpenSSL public key.
     * Requires PHP 8.1+ with OpenSSL 1.1.1+.
     */
    private static function importOkpJwk(array $jwk)
    {
        $crv = $jwk['crv'] ?? '';
        if ($crv !== 'Ed25519') {
            throw new \Exception('Unsupported OKP curve: ' . $crv . ' (only Ed25519 is supported)');
        }
        if (empty($jwk['x'])) {
            throw new \Exception('OKP JWK missing x parameter');
        }

        $x = self::base64urlDecode($jwk['x']);

        // Ed25519 SubjectPublicKeyInfo DER:
        // SEQUENCE { SEQUENCE { OID 1.3.101.112 } BIT STRING { key bytes } }
        $oid = pack('H*', '2b6570'); // OID 1.3.101.112 (Ed25519)
        $oidDer = pack('Ca*', 0x06, self::derEncodeLength(strlen($oid)) . $oid);
        $algSeq = pack('Ca*', 0x30, self::derEncodeLength(strlen($oidDer)) . $oidDer);
        $bitString = "\x00" . $x;
        $bitStr = pack('Ca*a*', 0x03, self::derEncodeLength(strlen($bitString)), $bitString);
        $spki = pack('Ca*a*', 0x30, self::derEncodeLength(strlen($algSeq . $bitStr)), $algSeq . $bitStr);

        $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($spki), 64, "\n") . "-----END PUBLIC KEY-----\n";
        $key = openssl_pkey_get_public($pem);
        if ($key === false) {
            throw new \Exception('EdDSA (Ed25519) is not supported by this PHP/OpenSSL version');
        }
        return $key;
    }

    /**
     * Verify a DPoP JWT signature.
     *
     * EC signatures in JWTs are raw R||S bytes; must convert to DER for OpenSSL.
     * RSA-PSS (PS256/384/512) requires special handling.
     *
     * @param  string $signingInput The header.payload string.
     * @param  string $signature    Raw decoded signature bytes.
     * @param  mixed  $publicKey    OpenSSL public key resource.
     * @param  array  $jwk         JWK array (used to determine key type).
     * @param  string $alg         JWT algorithm string.
     * @throws \Exception on verification failure.
     */
    private static function verifyDpopSignature(
        string $signingInput,
        string $signature,
        $publicKey,
        array $jwk,
        string $alg
    ): void {
        $kty = $jwk['kty'] ?? '';

        switch ($alg) {
            case 'RS256':
                $opensslAlg = OPENSSL_ALGO_SHA256;
                break;
            case 'RS384':
                $opensslAlg = OPENSSL_ALGO_SHA384;
                break;
            case 'RS512':
                $opensslAlg = OPENSSL_ALGO_SHA512;
                break;
            case 'ES256':
                $opensslAlg = OPENSSL_ALGO_SHA256;
                break;
            case 'ES384':
                $opensslAlg = OPENSSL_ALGO_SHA384;
                break;
            case 'ES512':
                $opensslAlg = OPENSSL_ALGO_SHA512;
                break;
            case 'PS256':
            case 'PS384':
            case 'PS512':
                self::verifyPss($signingInput, $signature, $publicKey, $alg);
                return;
            case 'EdDSA':
                self::verifyEdDsa($signingInput, $signature, $publicKey);
                return;
            default:
                throw new \Exception('Unsupported algorithm: ' . $alg);
        }

        if ($kty === 'EC') {
            // JWTs use raw R||S; OpenSSL needs DER-encoded ECDSA signature
            $signature = self::rawEcSigToDer($signature);
        }

        $result = openssl_verify($signingInput, $signature, $publicKey, $opensslAlg);
        if ($result !== 1) {
            throw new \Exception('DPoP proof signature verification failed');
        }
    }

    /**
     * Convert a raw R||S EC signature (as used in JWTs) to DER format.
     *
     * @throws \Exception if the signature length is unexpected.
     */
    private static function rawEcSigToDer(string $rawSig): string
    {
        $len = strlen($rawSig);
        if ($len % 2 !== 0) {
            throw new \Exception('Invalid EC signature length');
        }
        $half = $len / 2;
        $r = substr($rawSig, 0, $half);
        $s = substr($rawSig, $half);

        // Encode as ASN.1 INTEGER (prepend 0x00 if high bit set)
        $r = ltrim($r, "\x00");
        if (ord($r[0]) > 0x7f) {
            $r = "\x00" . $r;
        }
        $s = ltrim($s, "\x00");
        if (strlen($s) === 0 || ord($s[0]) > 0x7f) {
            $s = "\x00" . $s;
        }

        $rDer = pack('Ca*a*', 0x02, self::derEncodeLength(strlen($r)), $r);
        $sDer = pack('Ca*a*', 0x02, self::derEncodeLength(strlen($s)), $s);
        $seq = $rDer . $sDer;

        return pack('Ca*a*', 0x30, self::derEncodeLength(strlen($seq)), $seq);
    }

    /**
     * Verify RSA-PSS signature (PS256/384/512).
     *
     * Uses openssl_public_decrypt with PKCS1_PSS padding via raw OpenSSL.
     * PHP 7.x doesn't expose RSA-PSS directly in openssl_verify, so we
     * use openssl_public_decrypt with OPENSSL_PKCS1_OAEP_PADDING as a
     * workaround is NOT correct. Instead, PHP 8.x added support via EVP.
     * For broad compatibility we attempt openssl_verify with the PSS digest;
     * if not available, throw an informative error.
     *
     * @throws \Exception on verification failure or unsupported environment.
     */
    private static function verifyPss(string $signingInput, string $signature, $publicKey, string $alg): void
    {
        // Map alg to hash algorithm name
        $hashMap = ['PS256' => 'sha256', 'PS384' => 'sha384', 'PS512' => 'sha512'];
        $hashAlg = $hashMap[$alg];

        // PHP 8.x: openssl_verify supports RSA-PSS via algorithm string
        // We try using the OpenSSL algorithm identifier directly
        $opensslAlgMap = [
            'PS256' => defined('OPENSSL_ALGO_SHA256') ? 'SHA256' : null,
            'PS384' => defined('OPENSSL_ALGO_SHA384') ? 'SHA384' : null,
            'PS512' => defined('OPENSSL_ALGO_SHA512') ? 'SHA512' : null,
        ];

        // Use openssl_public_decrypt with manual PSS verification
        // Compute the digest of the signing input
        $digest = hash($hashAlg, $signingInput, true);
        $digestLen = strlen($digest);

        // Decrypt the signature using the RSA public key (raw RSA operation)
        $decrypted = '';
        $decryptResult = openssl_public_decrypt($signature, $decrypted, $publicKey, OPENSSL_NO_PADDING);
        if (!$decryptResult) {
            throw new \Exception('RSA-PSS signature verification failed (decrypt step)');
        }

        // Verify PSS encoding manually
        if (!self::emsaPssVerify($digest, $decrypted, $hashAlg)) {
            throw new \Exception('RSA-PSS signature verification failed');
        }
    }

    /**
     * EMSA-PSS verification per RFC 8017 §9.1.2.
     *
     * @param  string $mHash  Hash of the message.
     * @param  string $em     The decoded EM value (same byte length as modulus).
     * @param  string $hashAlg Hash algorithm name (e.g. 'sha256').
     * @return bool
     */
    private static function emsaPssVerify(string $mHash, string $em, string $hashAlg): bool
    {
        $hLen = strlen($mHash);
        $emLen = strlen($em);
        $sLen = $hLen; // salt length equals hash length (standard for JWT PS*)

        if ($emLen < $hLen + $sLen + 2) {
            return false;
        }

        // Last byte must be 0xbc
        if (ord($em[$emLen - 1]) !== 0xbc) {
            return false;
        }

        $maskedDB = substr($em, 0, $emLen - $hLen - 1);
        $h = substr($em, $emLen - $hLen - 1, $hLen);

        // Check leftmost bits are zero (emBits = emLen*8 - 1)
        if (ord($maskedDB[0]) & 0x80) {
            return false;
        }

        // Generate DB mask using MGF1
        $dbMask = self::mgf1($h, $emLen - $hLen - 1, $hashAlg);
        $db = $maskedDB ^ $dbMask;

        // Clear leftmost bit
        $db[0] = chr(ord($db[0]) & 0x7f);

        // Verify padding: emLen - hLen - sLen - 2 zero bytes followed by 0x01
        $padLen = $emLen - $hLen - $sLen - 2;
        for ($i = 0; $i < $padLen; $i++) {
            if (ord($db[$i]) !== 0x00) {
                return false;
            }
        }
        if (ord($db[$padLen]) !== 0x01) {
            return false;
        }

        $salt = substr($db, $padLen + 1);

        // Construct M' = 0x00 * 8 || mHash || salt
        $mPrime = str_repeat("\x00", 8) . $mHash . $salt;
        $hPrime = hash($hashAlg, $mPrime, true);

        return hash_equals($h, $hPrime);
    }

    /**
     * MGF1 mask generation function per RFC 8017.
     */
    private static function mgf1(string $seed, int $maskLen, string $hashAlg): string
    {
        $t = '';
        $hLen = strlen(hash($hashAlg, '', true));
        $ceiling = (int) ceil($maskLen / $hLen);
        for ($i = 0; $i < $ceiling; $i++) {
            $c = pack('N', $i);
            $t .= hash($hashAlg, $seed . $c, true);
        }
        return substr($t, 0, $maskLen);
    }

    /**
     * Verify an EdDSA (Ed25519) signature.
     *
     * @throws \Exception if not supported or verification fails.
     */
    private static function verifyEdDsa(string $signingInput, string $signature, $publicKey): void
    {
        if (!function_exists('openssl_sign') || PHP_VERSION_ID < 80100) {
            throw new \Exception('EdDSA requires PHP 8.1+ with OpenSSL 1.1.1+');
        }
        $result = openssl_verify($signingInput, $signature, $publicKey, OPENSSL_ALGO_SHA512);
        if ($result !== 1) {
            throw new \Exception('EdDSA signature verification failed');
        }
    }

    /**
     * Compute the JWK thumbprint (RFC 7638).
     *
     * @param  array $jwk The JWK array.
     * @return string Base64url-encoded SHA-256 thumbprint.
     * @throws \Exception on unsupported key type.
     */
    private static function computeJwkThumbprint(array $jwk): string
    {
        $kty = $jwk['kty'] ?? '';
        switch ($kty) {
            case 'EC':
                $members = [
                    'crv' => $jwk['crv'],
                    'kty' => 'EC',
                    'x'   => $jwk['x'],
                    'y'   => $jwk['y'],
                ];
                break;
            case 'RSA':
                $members = [
                    'e'   => $jwk['e'],
                    'kty' => 'RSA',
                    'n'   => $jwk['n'],
                ];
                break;
            case 'OKP':
                $members = [
                    'crv' => $jwk['crv'],
                    'kty' => 'OKP',
                    'x'   => $jwk['x'],
                ];
                break;
            default:
                throw new \Exception('Unsupported JWK key type for thumbprint: ' . $kty);
        }
        ksort($members); // alphabetical sort per RFC 7638
        $json = json_encode($members, JSON_UNESCAPED_SLASHES);
        return self::base64urlEncode(hash('sha256', $json, true));
    }

    /**
     * Check that the DPoP htu claim matches the request URL (RFC 9449 §4.2).
     *
     * - Strips query and fragment
     * - Normalises scheme and host to lowercase
     * - Strips default ports (80 for http, 443 for https)
     */
    private static function htuMatches(string $htu, string $requestUrl): bool
    {
        $htuParts = parse_url($htu);
        $reqParts = parse_url($requestUrl);

        if (!$htuParts || !$reqParts) {
            return false;
        }

        foreach (['scheme', 'host'] as $required) {
            if (empty($htuParts[$required]) || empty($reqParts[$required])) {
                return false;
            }
        }

        $normalize = function (array $parts): string {
            $scheme = strtolower($parts['scheme']);
            $host = strtolower($parts['host']);
            $port = $parts['port'] ?? null;
            $path = $parts['path'] ?? '/';

            // Strip default ports
            if ($port !== null) {
                if (($scheme === 'https' && $port == 443) || ($scheme === 'http' && $port == 80)) {
                    $port = null;
                }
            }

            $authority = $host . ($port !== null ? ':' . $port : '');
            return $scheme . '://' . $authority . $path;
        };

        return $normalize($htuParts) === $normalize($reqParts);
    }

    /**
     * DER-encode an ASN.1 length value.
     */
    private static function derEncodeLength(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }
        $temp = $length;
        $bytes = '';
        while ($temp > 0) {
            $bytes = chr($temp & 0xFF) . $bytes;
            $temp >>= 8;
        }
        return chr(0x80 | strlen($bytes)) . $bytes;
    }
}
