<?php

namespace Descope\SDK\Token;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Descope\SDK\Exception\TokenException;
use Descope\SDK\Configuration\SDKConfig;

final class Extractor
{
    private SDKConfig $config;

    /**
     * Constructor for Verifier class.
     *
     * @param SDKConfig $config Base configuration options for the SDK.
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Return an array representing the Token's claims, but only after the
     * token's signature and expiration have been cryptographically verified.
     *
     * The claims returned by this method can be trusted for authorization
     * decisions (roles, permissions, subject, tenants, etc.). A token with an
     * invalid or forged signature, an unknown key ID, or an expired lifetime
     * will cause a {@see TokenException} to be thrown instead of returning the
     * attacker-controlled payload.
     *
     * If you need to read claims WITHOUT verifying the signature (for example,
     * to inspect the `kid` before key resolution), use
     * {@see self::getClaimsUnverified()} and treat the result as untrusted.
     *
     * @param  string $sessionToken The session token to verify and decode.
     * @return array<string,mixed> The verified JWT claims.
     * @throws TokenException If the signature is invalid or the token is expired.
     */
    public function getClaims(string $sessionToken): array
    {
        $claims = $this->validateJWT($sessionToken);

        if (isset($claims['exp']) && time() > $claims['exp']) {
            throw new TokenException('Token has expired');
        }

        return $claims;
    }

    /**
     * Decode and return the Token's claims WITHOUT verifying its signature.
     *
     * WARNING: The returned claims are NOT trustworthy. The signature is not
     * checked, so any field (roles, permissions, sub, tenants, exp, ...) may
     * have been forged by the caller of your application. NEVER use the result
     * of this method for authentication or authorization decisions. Use
     * {@see self::getClaims()} for any trusted use.
     *
     * @param  string $sessionToken The session token to decode.
     * @return array<string,mixed> The unverified JWT claims.
     * @throws TokenException If the token is malformed.
     */
    public function getClaimsUnverified(string $sessionToken): array
    {
        $parts = $this->parseToken($sessionToken);
        return $parts['payload'] ?? [];
    }

    /**
     * Parse and validate the JWT token.
     *
     * @throws TokenException if validation fails.
     */
    public function parseToken(string $sessionToken): array
    {
        $parts = explode('.', $sessionToken);
        if (count($parts) !== 3) {
            throw new TokenException('Invalid JWT format');
        }

        $header = $this->decodeJWTPart($parts[0]);
        $payload = $this->decodeJWTPart($parts[1]);
        $signature = $this->base64UrlDecode($parts[2]);

        if (!isset($header['alg']) || $header['alg'] !== 'RS256') {
            throw new TokenException('Unsupported algorithm. Only RS256 is supported.');
        }

        return [
            'raw' => [
                'header' => $parts[0],
                'payload' => $parts[1],
                'signature' => $parts[2]
            ],
            'header' => $header,
            'payload' => $payload,
            'signature' => $signature
        ];
    }

    /**
     * Validate a JWT using the provided JWK Set.
     */
    public function validateJWT(string $sessionToken): array
    {
        // Parse and structurally validate the token before any network call:
        // the token's contents do not change between key-refresh retries, so a
        // malformed token or unsupported algorithm should fail fast without
        // fetching the JWKS.
        $jwt = $this->parseToken($sessionToken);

        if (!isset($jwt['header']['kid'])) {
            throw new TokenException('Missing key ID in JWT header');
        }

        $useRefreshedKey = false;
        do {
            try {
                $jwkSet = $this->config->getJWKSets($useRefreshedKey);

                $matchingKey = null;
                foreach ($jwkSet['keys'] as $key) {
                    if ($key['kid'] === $jwt['header']['kid']) {
                        $matchingKey = $key;
                        break;
                    }
                }

                if (!$matchingKey) {
                    throw new TokenException('No matching key found in JWKS');
                }

                $publicKeyPEM = $this->convertJWKToPEM($matchingKey);
                $signatureValid = $this->verifySignature(
                    $jwt['raw']['header'] . '.' . $jwt['raw']['payload'],
                    $jwt['signature'],
                    $publicKeyPEM
                );

                if (!$signatureValid) {
                    throw new TokenException('Invalid signature');
                }

                return $jwt['payload'];
            } catch (TokenException $e) {
                if ($useRefreshedKey) {
                    throw new TokenException('JWT validation failed after retry: ' . $e->getMessage());
                }
                $useRefreshedKey = true;
            }
        } while ($useRefreshedKey);

        throw new TokenException('JWT validation failed');
    }

    /**
     * Verify JWT signature.
     */
    private function verifySignature(string $signedData, string $signature, string $publicKeyPEM): bool
    {
        $publicKey = openssl_pkey_get_public($publicKeyPEM);
        if (!$publicKey) {
            throw new TokenException('Invalid public key');
        }

        $result = openssl_verify(
            $signedData,
            $signature,
            $publicKey,
            OPENSSL_ALGO_SHA256
        );

        return $result === 1;
    }

    /**
     * Convert JWK to PEM format.
     */
    private function convertJWKToPEM(array $jwk): string
    {
        if (!isset($jwk['kty']) || $jwk['kty'] !== 'RSA') {
            throw new TokenException('Invalid key type. Only RSA is supported.');
        }

        $modulus = $this->base64UrlDecode($jwk['n']);
        $exponent = $this->base64UrlDecode($jwk['e']);

        // Remove leading null bytes from modulus
        $modulus = ltrim($modulus, "\x00");

        // Construct RSA public key in ASN.1 format
        $modulus = pack('Ca*a*', 0x02, $this->encodeLength(strlen($modulus)), $modulus);
        $exponent = pack('Ca*a*', 0x02, $this->encodeLength(strlen($exponent)), $exponent);
        
        $rsaPublicKey = pack('Ca*a*', 0x30, $this->encodeLength(strlen($modulus . $exponent)), $modulus . $exponent);

        // Add RSA public key algorithm identifier
        $algorithmIdentifier = pack('H*', '300d06092a864886f70d0101010500');
        $bitString = pack('Ca*', 0x03, $this->encodeLength(strlen($rsaPublicKey) + 1) . "\x00" . $rsaPublicKey);
        
        $der = pack(
            'Ca*a*',
            0x30,
            $this->encodeLength(strlen($algorithmIdentifier . $bitString)),
            $algorithmIdentifier . $bitString
        );

        return sprintf(
            "-----BEGIN PUBLIC KEY-----\n%s-----END PUBLIC KEY-----\n",
            chunk_split(base64_encode($der), 64, "\n")
        );
    }


    /**
     * Helper to encode the length in DER format.
     */
    private function encodeLength(int $length): string
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

    /**
     * Decodes a Base64Url-encoded string.
     */
    private function base64UrlDecode(string $data): string
    {
        $padded = str_pad(
            strtr($data, '-_', '+/'),
            strlen($data) + (4 - strlen($data) % 4) % 4,
            '='
        );
        
        $decoded = base64_decode($padded, true);
        if ($decoded === false) {
            throw new TokenException('Invalid base64url encoding');
        }
        
        return $decoded;
    }

    private function decodeJWTPart(string $data): array
    {
        $decoded = $this->base64UrlDecode($data);
        $result = json_decode($decoded, true);
        
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new TokenException('Invalid JWT part encoding');
        }
        
        return $result;
    }
}
