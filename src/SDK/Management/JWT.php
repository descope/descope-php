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

    /**
     * Generate a step-up JWT for a given user, on behalf of an impersonator.
     *
     * The impersonator must have the required permissions in order to
     * impersonate the target user.
     *
     * @param string      $impersonatorId  The ID of the user performing the impersonation.
     * @param string      $loginId         The login ID of the user being impersonated.
     * @param bool        $validateConsent Whether to validate that consent to impersonate has been given.
     * @param array|null  $customClaims    Optional map of custom claims to add to the JWT.
     * @param string      $tenantId        Optional tenant to select for the impersonated session.
     * @param int         $refreshDuration Optional duration, in seconds, for the refreshed token.
     * @return string The impersonated, step-up signed JWT.
     * @throws AuthException If the impersonation request fails.
     */
    public function impersonateStepup(
        string $impersonatorId,
        string $loginId,
        bool $validateConsent = false,
        ?array $customClaims = null,
        string $tenantId = "",
        int $refreshDuration = 0
    ): string {
        $body = [
            'loginId' => $loginId,
            'impersonatorId' => $impersonatorId,
            'validateConsent' => $validateConsent,
            'customClaims' => $customClaims,
            'selectedTenant' => $tenantId,
            'refreshDuration' => $refreshDuration,
        ];

        $response = $this->api->doPost(MgmtV1::$IMPERSONATE_STEPUP_PATH, $body, true);

        return $response['jwt'] ?? '';
    }

    /**
     * Stop an active impersonation session, returning to the original user.
     *
     * @param string     $jwt             The JWT of the active impersonation session.
     * @param array|null $customClaims    Optional map of custom claims to add to the JWT.
     * @param string     $tenantId        Optional tenant to select for the resulting session.
     * @param int        $refreshDuration Optional duration, in seconds, for the refreshed token.
     * @return string The signed JWT for the original (impersonator) user.
     * @throws AuthException If the stop-impersonation request fails.
     */
    public function stopImpersonation(
        string $jwt,
        ?array $customClaims = null,
        string $tenantId = "",
        int $refreshDuration = 0
    ): string {
        $body = [
            'jwt' => $jwt,
            'customClaims' => $customClaims,
            'selectedTenant' => $tenantId,
            'refreshDuration' => $refreshDuration,
        ];

        $response = $this->api->doPost(MgmtV1::$STOP_IMPERSONATION_PATH, $body, true);

        return $response['jwt'] ?? '';
    }

    /**
     * Sign in as a given user, generating a session for them via the management API.
     *
     * @param string     $loginId      The login ID of the user to sign in.
     * @param array|null $loginOptions Optional login options (e.g. stepup, mfa,
     *                                 revokeOtherSessions, revokeOtherSessionsTypes,
     *                                 customClaims, jwt, refreshDuration).
     * @return array The authentication response (session/refresh JWTs).
     * @throws AuthException If the sign-in request fails.
     */
    public function signIn(string $loginId, ?array $loginOptions = null): array
    {
        $loginOptions = $loginOptions ?? [];

        $body = [
            'loginId' => $loginId,
            'stepup' => $loginOptions['stepup'] ?? false,
            'mfa' => $loginOptions['mfa'] ?? false,
            'revokeOtherSessions' => $loginOptions['revokeOtherSessions'] ?? false,
            'revokeOtherSessionsTypes' => $loginOptions['revokeOtherSessionsTypes'] ?? null,
            'customClaims' => $loginOptions['customClaims'] ?? null,
            'jwt' => $loginOptions['jwt'] ?? '',
            'refreshDuration' => $loginOptions['refreshDuration'] ?? 0,
        ];

        return $this->api->doPost(MgmtV1::$MGMT_SIGN_IN_PATH, $body, true);
    }

    /**
     * Sign up a new user, generating a session for them via the management API.
     *
     * @param string     $loginId       The login ID of the user to sign up.
     * @param array|null $user          Optional user details (e.g. email, phone, name,
     *                                  emailVerified, phoneVerified, ssoAppId).
     * @param array|null $signUpOptions Optional sign-up options (customClaims, refreshDuration).
     * @return array The authentication response (session/refresh JWTs).
     * @throws AuthException If the sign-up request fails.
     */
    public function signUp(string $loginId, ?array $user = null, ?array $signUpOptions = null): array
    {
        return $this->doSignUp(MgmtV1::$MGMT_SIGN_UP_PATH, $loginId, $user, $signUpOptions);
    }

    /**
     * Sign up a new user or sign in an existing one, generating a session via the management API.
     *
     * @param string     $loginId       The login ID of the user.
     * @param array|null $user          Optional user details (e.g. email, phone, name,
     *                                  emailVerified, phoneVerified, ssoAppId).
     * @param array|null $signUpOptions Optional sign-up options (customClaims, refreshDuration).
     * @return array The authentication response (session/refresh JWTs).
     * @throws AuthException If the sign-up-or-in request fails.
     */
    public function signUpOrIn(string $loginId, ?array $user = null, ?array $signUpOptions = null): array
    {
        return $this->doSignUp(MgmtV1::$MGMT_SIGN_UP_OR_IN_PATH, $loginId, $user, $signUpOptions);
    }

    /**
     * Shared sign-up implementation for the signUp and signUpOrIn endpoints.
     *
     * @param string     $path          The management path to post to.
     * @param string     $loginId       The login ID of the user.
     * @param array|null $user          Optional user details.
     * @param array|null $signUpOptions Optional sign-up options (customClaims, refreshDuration).
     * @return array The authentication response (session/refresh JWTs).
     * @throws AuthException If the request fails.
     */
    private function doSignUp(string $path, string $loginId, ?array $user, ?array $signUpOptions): array
    {
        $user = $user ?? [];
        $signUpOptions = $signUpOptions ?? [];

        // emailVerified/phoneVerified/ssoAppId are top-level siblings of the user object in Go SDK,
        // not nested inside it. Lift them out and drop them from the nested user to avoid sending
        // duplicate/unknown fields inside the user object.
        $emailVerified = $user['emailVerified'] ?? false;
        $phoneVerified = $user['phoneVerified'] ?? false;
        $ssoAppId = $user['ssoAppId'] ?? '';
        unset($user['emailVerified'], $user['phoneVerified'], $user['ssoAppId']);

        $body = [
            'loginId' => $loginId,
            'user' => $user,
            'emailVerified' => $emailVerified,
            'phoneVerified' => $phoneVerified,
            'ssoAppId' => $ssoAppId,
            'customClaims' => $signUpOptions['customClaims'] ?? null,
            'refreshDuration' => $signUpOptions['refreshDuration'] ?? 0,
        ];

        return $this->api->doPost($path, $body, true);
    }

    /**
     * Generate an anonymous JWT (no associated user), optionally scoped to a tenant.
     *
     * @param array|null $customClaims    Optional map of custom claims to add to the JWT.
     * @param string     $selectedTenant  Optional tenant to select for the anonymous session.
     * @param int        $refreshDuration Optional duration, in seconds, for the refreshed token.
     * @return array The authentication response (session/refresh JWTs).
     * @throws AuthException If the anonymous request fails.
     */
    public function anonymous(?array $customClaims = null, string $selectedTenant = "", int $refreshDuration = 0): array
    {
        $body = [
            'selectedTenant' => $selectedTenant,
            'customClaims' => $customClaims,
            'refreshDuration' => $refreshDuration,
        ];

        return $this->api->doPost(MgmtV1::$MGMT_ANONYMOUS_PATH, $body, true);
    }
}
