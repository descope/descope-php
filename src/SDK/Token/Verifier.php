<?php

namespace Descope\SDK\Token;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWKSet;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Descope\SDK\Token\Extractor;
use Descope\SDK\Configuration\SDKConfig;
use Descope\SDK\EndpointsV1;

final class Verifier
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
     * Returns true if the JWT signature is valid and not expired.
     *
     * @param  string $sessionToken The session token.
     * @return boolean Token signature is valid and not expired.
     * @throws AuthException If the refresh operation fails.
     */
    public function verify($sessionToken, ?string $audience = null)
    {
        try {
            $extractor = new Extractor($this->config);
            $jws = $extractor->parseToken($sessionToken);

            // If JWT signature is valid
            if (isset($jws)) {
                $payload = json_decode($jws->getPayload());

                // Check to make sure JWT is not expired
                if (isset($payload->exp) && time() < $payload->exp) {
                    if ($audience && (!isset($payload->aud) || $payload->aud !== $audience)) {
                        return false;
                    }
                    return true;
                }
            }
            return false;
        } catch (TokenException $te) {
            throw TokenException::MSG_SIGNATURE_INVALID;
        }
    }

    /**
     * Refreshes the session token, with the provided refresh token.
     *
     * @param  string $refreshToken The refresh token.
     * @return array The refreshed JWT response.
     * @throws AuthException If the refresh operation fails.
     */
    public function refreshSession(string $refreshToken): array
    {
        $this->validateRefreshTokenNotNil($refreshToken);
        $this->validateToken($refreshToken);
        $uri = EndpointsV1::REFRESH_TOKEN_PATH;
        $response = $this->doPost($uri, [], $refreshToken);
        return $this->generateJwtResponse($response, $refreshToken);
    }

    /**
     * Verifies the session token, and automatically refreshes when expired.
     *
     * @param  string $sessionToken The session token.
     * @param  string $refreshToken The refresh token.
     * @return array The JWT response.
     * @throws AuthException If both tokens are missing or verification fails.
     */
    public function verifyAndRefreshSession(string $sessionToken, string $refreshToken): array
    {
        if (empty($sessionToken)) {
            throw new AuthException(400, 'Session token is missing');
        }

        try {
            $this->validateToken($sessionToken);
            return $this->generateJwtResponse($sessionToken, $refreshToken);
        } catch (AuthException $e) {
            return $this->refreshSession($refreshToken);
        }
    }

    /**
     * Validates permissions for a JWT response.
     *
     * @param  array $jwtResponse JWT response data.
     * @param  array $permissions Permissions to validate.
     * @return bool True if permissions are valid, false otherwise.
     */
    public function validatePermissions(array $jwtResponse, array $permissions): bool
    {
        return $this->validateTenantPermissions($jwtResponse, '', $permissions);
    }

    /**
     * Validates tenant permissions for a JWT response.
     *
     * @param  array  $jwtResponse JWT response data.
     * @param  string $tenant      Tenant ID.
     * @param  array  $permissions Permissions to validate.
     * @return bool True if tenant permissions are valid, false otherwise.
     * @throws AuthException If JWT response is invalid.
     */
    public function validateTenantPermissions(array $jwtResponse, string $tenant, array $permissions): bool
    {
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        if (!is_array($jwtResponse)) {
            throw new AuthException(400, 'Invalid JWT response hash');
        }

        $grantedPermissions = $jwtResponse['permissions'] ?? [];
        if (!empty($tenant)) {
            if (empty($jwtResponse['tenants'][$tenant])) {
                return false;
            }
            $grantedPermissions = $jwtResponse['tenants'][$tenant]['permissions'] ?? [];
        }

        return empty(array_diff($permissions, $grantedPermissions));
    }

    /**
     * Validates roles for a JWT response.
     *
     * @param  array $jwtResponse JWT response data.
     * @param  array $roles       Roles to validate.
     * @return bool True if roles are valid, false otherwise.
     */
    public function validateRoles(array $jwtResponse, array $roles): bool
    {
        return $this->validateTenantRoles($jwtResponse, '', $roles);
    }

    /**
     * Validates tenant roles for a JWT response.
     *
     * @param  array  $jwtResponse JWT response data.
     * @param  string $tenant      Tenant ID.
     * @param  array  $roles       Roles to validate.
     * @return bool True if tenant roles are valid, false otherwise.
     * @throws AuthException If JWT response is invalid.
     */
    public function validateTenantRoles(array $jwtResponse, string $tenant, array $roles): bool
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if (!is_array($jwtResponse)) {
            throw new AuthException(400, 'Invalid JWT response hash');
        }

        $grantedRoles = $jwtResponse['roles'] ?? [];
        if (!empty($tenant)) {
            if (empty($jwtResponse['tenants'][$tenant])) {
                return false;
            }
            $grantedRoles = $jwtResponse['tenants'][$tenant]['roles'] ?? [];
        }

        return empty(array_diff($roles, $grantedRoles));
    }
}
