<?php

namespace Descope\SDK\Management;

use Descope\SDK\API;
use Descope\SDK\Exception\AuthException;
use Descope\SDK\Management\MgmtV1;

/**
 * Class OutboundApps
 *
 * Manages outbound application tokens for Descope.
 * Outbound apps allow users to authenticate with third-party services through Descope.
 */
class OutboundApps
{
    private API $api;

    /**
     * OutboundApps constructor.
     *
     * @param API $api The API instance to be used for making requests.
     */
    public function __construct(API $api)
    {
        $this->api = $api;
    }

    /**
     * Create a new outbound application.
     *
     * Mirrors the go-sdk CreateApplication request: the app fields are sent at the top level
     * of the body alongside a clientSecret key.
     *
     * @param array $appRequest The outbound application definition. Recognized keys:
     *                          id, name, description, templateId, clientId, logo,
     *                          discoveryUrl, authorizationUrl, authorizationUrlParams,
     *                          tokenUrl, tokenUrlParams, revocationUrl, defaultScopes,
     *                          defaultRedirectUrl, callbackDomain, pkce, accessType,
     *                          prompt, clientSecret.
     *
     * @return array The response containing the created application under the 'app' key.
     *
     * @throws AuthException If the create operation fails.
     */
    public function createApplication(array $appRequest): array
    {
        $body = $this->buildAppRequest($appRequest);
        $body['clientSecret'] = $appRequest['clientSecret'] ?? '';

        return $this->api->doPost(
            MgmtV1::$OUTBOUND_APP_CREATE_PATH,
            $body,
            true
        );
    }

    /**
     * Update an existing outbound application.
     *
     * Mirrors the go-sdk UpdateApplication request: the app fields are nested under an 'app'
     * key, and an optional clientSecret is added to the app body when provided.
     *
     * @param array       $appRequest   The outbound application definition (must include id and name).
     *                                  Recognized keys match createApplication.
     * @param string|null $clientSecret Optional client secret to update. When null the key is omitted.
     *
     * @return array The response containing the updated application under the 'app' key.
     *
     * @throws AuthException If the update operation fails.
     */
    public function updateApplication(array $appRequest, ?string $clientSecret = null): array
    {
        $app = $this->buildAppRequest($appRequest);
        if ($clientSecret !== null) {
            $app['clientSecret'] = $clientSecret;
        }

        return $this->api->doPost(
            MgmtV1::$OUTBOUND_APP_UPDATE_PATH,
            ['app' => $app],
            true
        );
    }

    /**
     * Delete an outbound application by its ID.
     *
     * @param string $id The ID of the outbound application to delete.
     *
     * @return void
     *
     * @throws AuthException If the delete operation fails.
     */
    public function deleteApplication(string $id): void
    {
        $this->api->doPost(
            MgmtV1::$OUTBOUND_APP_DELETE_PATH,
            ['id' => $id],
            true
        );
    }

    /**
     * Load a single outbound application by its ID.
     *
     * Matches the go-sdk LoadApplication, which appends the app ID as a path segment to the
     * load path.
     *
     * @param string $id The ID of the outbound application to load.
     *
     * @return array The response containing the application under the 'app' key.
     *
     * @throws AuthException If the load operation fails.
     */
    public function loadApplication(string $id): array
    {
        $url = MgmtV1::$OUTBOUND_APP_LOAD_PATH . '/' . rawurlencode($id);

        return $this->api->doGet($url, true);
    }

    /**
     * Load all outbound applications.
     *
     * @return array The response containing the applications under the 'apps' key.
     *
     * @throws AuthException If the load operation fails.
     */
    public function loadAllApplications(): array
    {
        return $this->api->doGet(MgmtV1::$OUTBOUND_APP_LOAD_ALL_PATH, true);
    }

    /**
     * Fetch the latest outbound application user token.
     *
     * The "latest" endpoint ignores scopes, so any 'scopes' key in the request is stripped
     * to avoid posting a field the target message does not define (the gateway rejects
     * unknown fields), matching the go-sdk FetchLatestUserToken behavior.
     *
     * @param array $request The request body. Recognized keys: appId, userId, tenantId, options.
     *
     * @return array The token response containing the token and metadata.
     *
     * @throws AuthException If the fetch operation fails.
     */
    public function fetchLatestUserToken(array $request): array
    {
        $body = ['appId' => $request['appId'] ?? '', 'userId' => $request['userId'] ?? ''];
        if (!empty($request['tenantId'])) {
            $body['tenantId'] = $request['tenantId'];
        }
        if (isset($request['options'])) {
            $body['options'] = $request['options'];
        }

        $response = $this->api->doPost(
            MgmtV1::$OUTBOUND_APP_FETCH_LATEST_USER_TOKEN_PATH,
            $body,
            true
        );

        return $this->convertTokenResponse($response);
    }

    /**
     * Fetch an outbound application tenant token with the specified scopes.
     *
     * @param array $request The request body. Recognized keys: appId, tenantId, scopes, options.
     *
     * @return array The token response containing the token and metadata.
     *
     * @throws AuthException If the fetch operation fails.
     */
    public function fetchTenantToken(array $request): array
    {
        $body = ['appId' => $request['appId'] ?? '', 'tenantId' => $request['tenantId'] ?? ''];
        if (isset($request['scopes'])) {
            $body['scopes'] = $request['scopes'];
        }
        if (isset($request['options'])) {
            $body['options'] = $request['options'];
        }

        $response = $this->api->doPost(
            MgmtV1::$OUTBOUND_APP_FETCH_TENANT_TOKEN_PATH,
            $body,
            true
        );

        return $this->convertTokenResponse($response);
    }

    /**
     * Fetch the latest outbound application tenant token.
     *
     * The "latest" endpoint ignores scopes, so any 'scopes' key in the request is stripped,
     * matching the go-sdk FetchLatestTenantToken behavior.
     *
     * @param array $request The request body. Recognized keys: appId, tenantId, options.
     *
     * @return array The token response containing the token and metadata.
     *
     * @throws AuthException If the fetch operation fails.
     */
    public function fetchLatestTenantToken(array $request): array
    {
        $body = ['appId' => $request['appId'] ?? '', 'tenantId' => $request['tenantId'] ?? ''];
        if (isset($request['options'])) {
            $body['options'] = $request['options'];
        }

        $response = $this->api->doPost(
            MgmtV1::$OUTBOUND_APP_FETCH_LATEST_TENANT_TOKEN_PATH,
            $body,
            true
        );

        return $this->convertTokenResponse($response);
    }

    /**
     * List the outbound application IDs that have a token for the given user.
     *
     * @param string $userId   The ID of the user (required).
     * @param string $tenantId The tenant ID to filter by. Pass an empty string to omit it.
     *
     * @return array The response containing the application IDs under the 'appIds' key.
     *
     * @throws AuthException If the list operation fails.
     */
    public function listAppsWithUserToken(string $userId, string $tenantId): array
    {
        $queryParams = ['userId' => $userId];
        if ($tenantId !== '') {
            $queryParams['tenantId'] = $tenantId;
        }

        $url = MgmtV1::$OUTBOUND_APP_LIST_APPS_WITH_USER_TOKEN_PATH . '?' . http_build_query($queryParams);

        return $this->api->doGet($url, true);
    }

    /**
     * Upload/set a static API key for a user on an apikey-type outbound application.
     *
     * @param array $request The request body. Recognized keys: appId, userId, apiKey, tenantId.
     *
     * @return void
     *
     * @throws AuthException If the upload operation fails.
     */
    public function uploadUserApiKey(array $request): void
    {
        $body = [
            'appId' => $request['appId'] ?? '',
            'userId' => $request['userId'] ?? '',
            'apiKey' => $request['apiKey'] ?? '',
        ];
        if (!empty($request['tenantId'])) {
            $body['tenantId'] = $request['tenantId'];
        }

        $this->api->doPost(
            MgmtV1::$OUTBOUND_APP_UPLOAD_USER_API_KEY_PATH,
            $body,
            true
        );
    }

    /**
     * Upload/set a static API key for a tenant on an apikey-type outbound application.
     *
     * @param array $request The request body. Recognized keys: appId, tenantId, apiKey.
     *
     * @return void
     *
     * @throws AuthException If the upload operation fails.
     */
    public function uploadTenantApiKey(array $request): void
    {
        $body = [
            'appId' => $request['appId'] ?? '',
            'tenantId' => $request['tenantId'] ?? '',
            'apiKey' => $request['apiKey'] ?? '',
        ];

        $this->api->doPost(
            MgmtV1::$OUTBOUND_APP_UPLOAD_TENANT_API_KEY_PATH,
            $body,
            true
        );
    }

    /**
     * Upload a single OAuth token for a user.
     *
     * At least one of refreshToken or accessToken should be provided. When verifyRefresh is
     * true the refresh token is verified against the provider before persisting.
     *
     * @param array $request The request body. Recognized keys: appId, userId, tenantId,
     *                       refreshToken, accessToken, accessTokenExpiry, accessTokenType,
     *                       scopes, externalIdentifier, idToken, grantedBy, verifyRefresh.
     *
     * @return void
     *
     * @throws AuthException If the upload operation fails.
     */
    public function uploadUserToken(array $request): void
    {
        $this->api->doPost(
            MgmtV1::$OUTBOUND_APP_UPLOAD_USER_TOKEN_PATH,
            $request,
            true
        );
    }

    /**
     * Upload a single OAuth token for a tenant.
     *
     * At least one of refreshToken or accessToken should be provided. When verifyRefresh is
     * true the refresh token is verified against the provider before persisting.
     *
     * @param array $request The request body. Recognized keys: appId, tenantId, refreshToken,
     *                       accessToken, accessTokenExpiry, accessTokenType, scopes,
     *                       externalIdentifier, idToken, grantedBy, verifyRefresh.
     *
     * @return void
     *
     * @throws AuthException If the upload operation fails.
     */
    public function uploadTenantToken(array $request): void
    {
        $this->api->doPost(
            MgmtV1::$OUTBOUND_APP_UPLOAD_TENANT_TOKEN_PATH,
            $request,
            true
        );
    }

    /**
     * Batch upload OAuth tokens for users.
     *
     * Batch upload is all-or-nothing: a non-empty 'failures' slice in the response means no
     * tokens were committed.
     *
     * @param array $tokens A list of user token entries to upload. Each entry recognizes the
     *                     keys: appId, userId, tenantId, refreshToken, accessToken,
     *                     accessTokenExpiry, accessTokenType, scopes, externalIdentifier,
     *                     idToken, grantedBy.
     *
     * @return array The response containing per-item failures under the 'failures' key.
     *
     * @throws AuthException If the batch upload operation fails.
     */
    public function batchUploadUserTokens(array $tokens): array
    {
        return $this->api->doPost(
            MgmtV1::$OUTBOUND_APP_BATCH_UPLOAD_USER_TOKENS_PATH,
            ['tokens' => $tokens],
            true
        );
    }

    /**
     * Batch upload OAuth tokens for tenants.
     *
     * Batch upload is all-or-nothing: a non-empty 'failures' slice in the response means no
     * tokens were committed.
     *
     * @param array $tokens A list of tenant token entries to upload. Each entry recognizes the
     *                     keys: appId, tenantId, refreshToken, accessToken, accessTokenExpiry,
     *                     accessTokenType, scopes, externalIdentifier, idToken, grantedBy.
     *
     * @return array The response containing per-item failures under the 'failures' key.
     *
     * @throws AuthException If the batch upload operation fails.
     */
    public function batchUploadTenantTokens(array $tokens): array
    {
        return $this->api->doPost(
            MgmtV1::$OUTBOUND_APP_BATCH_UPLOAD_TENANT_TOKENS_PATH,
            ['tokens' => $tokens],
            true
        );
    }

    /**
     * Build the create/update outbound application request body.
     *
     * Mirrors the go-sdk makeCreateUpdateOutboundApplicationRequest: it emits every app field
     * key explicitly, falling back to sensible zero values when a key is absent.
     *
     * @param array $app The outbound application definition.
     *
     * @return array The normalized app request body with all app keys.
     */
    private function buildAppRequest(array $app): array
    {
        return [
            'id' => $app['id'] ?? '',
            'name' => $app['name'] ?? '',
            'description' => $app['description'] ?? '',
            'templateId' => $app['templateId'] ?? '',
            'clientId' => $app['clientId'] ?? '',
            'logo' => $app['logo'] ?? '',
            'discoveryUrl' => $app['discoveryUrl'] ?? '',
            'authorizationUrl' => $app['authorizationUrl'] ?? '',
            'authorizationUrlParams' => $app['authorizationUrlParams'] ?? null,
            'tokenUrl' => $app['tokenUrl'] ?? '',
            'tokenUrlParams' => $app['tokenUrlParams'] ?? null,
            'revocationUrl' => $app['revocationUrl'] ?? '',
            'defaultScopes' => $app['defaultScopes'] ?? null,
            'defaultRedirectUrl' => $app['defaultRedirectUrl'] ?? '',
            'callbackDomain' => $app['callbackDomain'] ?? '',
            'pkce' => $app['pkce'] ?? false,
            'accessType' => $app['accessType'] ?? '',
            'prompt' => $app['prompt'] ?? null,
        ];
    }

    /**
     * Fetch an outbound application user token with the specified scopes.
     *
     * This method retrieves an access token for a user to interact with a third-party
     * outbound application. The token can be used to make authenticated requests to
     * the external service.
     *
     * @param string      $appId           The ID of the outbound application.
     * @param string      $userId          The ID of the user requesting the token.
     * @param array|null  $scopes          Optional list of scopes to request for the token.
     * @param bool        $withRefreshToken Whether to include a refresh token in the response.
     * @param bool        $forceRefresh    Whether to force a token refresh even if current token is valid.
     * @param string|null $tenantId        Optional tenant ID for multi-tenant applications.
     *
     * @return array The token response containing access token and metadata.
     *
     * @throws AuthException If the token fetch operation fails.
     */
    public function fetchUserToken(
        string $appId,
        string $userId,
        ?array $scopes = null,
        bool $withRefreshToken = false,
        bool $forceRefresh = false,
        ?string $tenantId = null
    ): array {
        $body = [
            'appId' => $appId,
            'userId' => $userId,
            'options' => [
                'withRefreshToken' => $withRefreshToken,
                'forceRefresh' => $forceRefresh,
            ],
        ];

        if ($scopes !== null) {
            $body['scopes'] = $scopes;
        }

        if ($tenantId !== null) {
            $body['tenantId'] = $tenantId;
        }

        $response = $this->api->doPost(
            MgmtV1::$OUTBOUND_APP_USER_TOKEN_PATH,
            $body,
            true
        );

        return $this->convertTokenResponse($response);
    }

    /**
     * Delete outbound application tokens by app ID and/or user ID.
     *
     * This method removes all tokens associated with a specific outbound application
     * and/or user. At least one of appId or userId must be provided.
     *
     * @param string|null $appId  Optional app ID to filter tokens to delete.
     * @param string|null $userId Optional user ID to filter tokens to delete.
     *
     * @return void
     *
     * @throws AuthException If the delete operation fails.
     */
    public function deleteUserTokens(?string $appId = null, ?string $userId = null): void
    {
        $queryParams = [];

        if ($appId !== null) {
            $queryParams['appId'] = $appId;
        }

        if ($userId !== null) {
            $queryParams['userId'] = $userId;
        }

        $url = MgmtV1::$OUTBOUND_APP_DELETE_USER_TOKENS_PATH;
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        $this->api->doDelete($url);
    }

    /**
     * Delete a specific outbound application token by its ID.
     *
     * This method removes a single token identified by its unique token ID.
     *
     * @param string $tokenId The unique ID of the token to delete.
     *
     * @return void
     *
     * @throws AuthException If the delete operation fails.
     */
    public function deleteTokenById(string $tokenId): void
    {
        $url = MgmtV1::$OUTBOUND_APP_DELETE_TOKEN_BY_ID_PATH . '?' . http_build_query(['id' => $tokenId]);

        $this->api->doDelete($url);
    }

    /**
     * Convert the API token response to a structured array.
     *
     * @param array $response The raw API response.
     *
     * @return array The structured token response.
     */
    private function convertTokenResponse(array $response): array
    {
        $token = $response['token'] ?? [];

        return [
            'token' => [
                'id' => $token['id'] ?? '',
                'appId' => $token['appId'] ?? '',
                'userId' => $token['userId'] ?? '',
                'tokenSub' => $token['tokenSub'] ?? '',
                'accessToken' => $token['accessToken'] ?? '',
                'accessTokenType' => $token['accessTokenType'] ?? '',
                'accessTokenExpiry' => $token['accessTokenExpiry'] ?? '',
                'hasRefreshToken' => $token['hasRefreshToken'] ?? false,
                'refreshToken' => $token['refreshToken'] ?? '',
                'lastRefreshTime' => $token['lastRefreshTime'] ?? '',
                'lastRefreshError' => $token['lastRefreshError'] ?? '',
                'scopes' => $token['scopes'] ?? [],
                'tenantId' => $token['tenantId'] ?? '',
                'grantedBy' => $token['grantedBy'] ?? '',
            ],
        ];
    }
}
