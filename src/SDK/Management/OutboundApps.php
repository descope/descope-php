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
