<?php

namespace Descope\SDK\Management;

use Descope\SDK\API;
use Descope\SDK\Exception\AuthException;
use Descope\SDK\Management\MgmtV1;

/**
 * Provides functions for managing JWTs in the Descope platform.
 *
 * This includes updating an existing JWT with custom claims and generating
 * an impersonated JWT on behalf of another user.
 */
class JWT
{
    private API $api;

    /**
     * Constructor for the JWT class.
     *
     * @param API $api The API instance used to make HTTP requests.
     */
    public function __construct(API $api)
    {
        $this->api = $api;
    }

    /**
     * Update a valid JWT with the custom claims provided.
     *
     * The new JWT will be signed with the latest signing key. It is up to the
     * caller to make sure the provided JWT is valid, as its claims will be used
     * as the basis for the newly generated token.
     *
     * @param string   $jwt             The existing valid JWT to update.
     * @param array    $customClaims    A map of custom claims to add to the JWT.
     * @param int|null $refreshDuration Optional duration, in seconds, for the
     *                                  refreshed token. When null, it is omitted.
     * @return string The updated, signed JWT.
     * @throws AuthException If the update request fails.
     */
    public function updateJWT(string $jwt, array $customClaims = [], ?int $refreshDuration = null): string
    {
        $body = [
            'jwt' => $jwt,
            'customClaims' => $customClaims,
        ];

        if ($refreshDuration !== null) {
            $body['refreshDuration'] = $refreshDuration;
        }

        $response = $this->api->doPost(MgmtV1::$UPDATE_JWT_PATH, $body, true);

        return $response['jwt'] ?? '';
    }

    /**
     * Generate a JWT for a given user, on behalf of an impersonator.
     *
     * The impersonator must have the required permissions in order to
     * impersonate the target user.
     *
     * @param string      $impersonatorId  The ID of the user performing the impersonation.
     * @param string      $loginId         The login ID of the user being impersonated.
     * @param bool        $validateConsent Whether to validate that consent to impersonate has been given.
     * @param array       $customClaims    A map of custom claims to add to the JWT.
     * @param string|null $selectedTenant  Optional tenant to select for the impersonated session. When null, it is omitted.
     * @param int|null    $refreshDuration Optional duration, in seconds, for the refreshed token. When null, it is omitted.
     * @return string The impersonated, signed JWT.
     * @throws AuthException If the impersonation request fails.
     */
    public function impersonate(
        string $impersonatorId,
        string $loginId,
        bool $validateConsent = true,
        array $customClaims = [],
        ?string $selectedTenant = null,
        ?int $refreshDuration = null
    ): string {
        $body = [
            'impersonatorId' => $impersonatorId,
            'loginId' => $loginId,
            'validateConsent' => $validateConsent,
            'customClaims' => $customClaims,
        ];

        if ($selectedTenant !== null) {
            $body['selectedTenant'] = $selectedTenant;
        }

        if ($refreshDuration !== null) {
            $body['refreshDuration'] = $refreshDuration;
        }

        $response = $this->api->doPost(MgmtV1::$IMPERSONATE_PATH, $body, true);

        return $response['jwt'] ?? '';
    }
}
