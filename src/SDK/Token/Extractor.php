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
     * Return an array representing the validated Token's claims.
     *
     * @return array<string,mixed>
     */
    public function getClaims(string $sessionToken): array
    {
        return $this->validateJWT($sessionToken);
    }

    /**
     * Parse the JWT token structure.
     *
     * @throws TokenException if validation fails.
     */
    private function parseToken(string $sessionToken): array
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
        $jwt = $this->parseToken($sessionToken);

        if (!isset($jwt['header']['kid'])) {
            throw new TokenException('Missing key ID in JWT header');
        }

        $useRefreshedKey = false;
        do {
            $jwkSet = $this->config->getJWKSets($useRefreshedKey);

            $matchingKey = null;
            foreach ($jwkSet['keys'] as $key) {
                if ($key['kid'] === $jwt['header']['kid']) {
                    $matchingKey = $key;
                    break;
                }
            }

            if (!$matchingKey) {
                if ($useRefreshedKey) {
                    throw new TokenException('JWT validation failed after retry: No matching key found in JWKS');
                }
                $useRefreshedKey = true;
                continue;
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

            if (isset($jwt['payload']['exp']) && time() > $jwt['payload']['exp']) {
                throw new TokenException('Token has expired');
            }

            $this->assertIssuerMatchesProject($jwt['payload']);

            return $jwt['payload'];
        } while ($useRefreshedKey);

        throw new TokenException('JWT validation failed');
    }

    /**
     * Ensures the token issuer resolves to the project the SDK is configured for.
     * Mirrors node-sdk's issuerMatchesProject: Descope issuers are the bare
     * project ID, a URL whose last path segment is the project ID
     * (https://api.descope.com/{projectId}), or a URL where the project ID is
     * followed by one more segment, such as federated OIDC apps using an
     * app-scoped issuer (https://api.descope.com/{projectId}/{appId}).
     *
     * @throws TokenException if the issuer does not match the configured project ID.
     */
    private function assertIssuerMatchesProject(array $payload): void
    {
        $projectId = $this->config->projectId;
        if (empty($projectId)) {
            return;
        }

        $issuer = $payload['iss'] ?? '';
        if (!is_string($issuer) || $issuer === '') {
            throw new TokenException('Token is missing issuer claim');
        }

        if (!self::issuerMatchesProject($issuer, $projectId)) {
            throw new TokenException('Token issuer does not match the configured project ID');
        }
    }

    /**
     * True if the issuer names the project ID as its last or second-to-last
     * path segment (or is exactly the project ID).
     */
    private static function issuerMatchesProject(string $issuer, string $projectId): bool
    {
        if ($issuer === $projectId) {
            return true;
        }

        // For URL issuers only the path is considered, so the host can never
        // stand in for the project ID.
        $path = $issuer;
        if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $issuer)) {
            $path = (string) parse_url($issuer, PHP_URL_PATH);
        }

        $segments = array_values(array_filter(
            explode('/', $path),
            static fn (string $segment): bool => $segment !== ''
        ));
        $count = count($segments);

        return ($count >= 1 && $segments[$count - 1] === $projectId)
            || ($count >= 2 && $segments[$count - 2] === $projectId);
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
