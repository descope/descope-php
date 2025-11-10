<?php

declare(strict_types=1);

namespace Descope\SDK\Auth;

use Descope\SDK\Exception\AuthException;
use Descope\SDK\EndpointsV1;
use Descope\SDK\API;

class OAuth
{
    /**
     * @var API The API object for making authenticated requests.
     */
    private $api;

    /**
     * Constructor for OAuth class.
     *
     * @param API $api API object for making authenticated requests.
     */
    public function __construct(API $api)
    {
        $this->api = $api;
    }

    /**
     * Starts OAuth authentication flow.
     *
     * @param  string                    $provider     OAuth provider (e.g., "google", "facebook").
     * @param  string                    $returnUrl    Optional redirect URL after authentication.
     * @param  \Descope\SDK\LoginOptions|null $loginOptions Optional login options.
     * @param  string|null               $refreshToken Optional refresh token for step-up/MFA.
     * @return array Response array containing the OAuth authorization URL.
     * @throws AuthException
     */
    public function start(string $provider, string $returnUrl = "", ?\Descope\SDK\LoginOptions $loginOptions = null, ?string $refreshToken = null): array
    {
        if (!$this->verifyProvider($provider)) {
            throw new AuthException(
                400,
                'invalid argument',
                "Unknown OAuth provider: $provider"
            );
        }

        \Descope\SDK\validate_refresh_token_provided($loginOptions, $refreshToken);

        $uri = $this->composeStartUrl($provider, $returnUrl);
        $body = $this->composeStartBody($loginOptions);

        $response = $this->api->doPost($uri, $body, false, $refreshToken);

        return $response;
    }

    /**
     * Exchanges OAuth authorization code for tokens.
     *
     * @param  string $code The authorization code from OAuth provider.
     * @return array Response array containing JWT tokens and user information.
     * @throws AuthException
     */
    public function exchangeToken(string $code): array
    {
        if (empty($code)) {
            throw new AuthException(400, 'invalid argument', 'code cannot be empty');
        }

        $uri = EndpointsV1::$OAUTH_EXCHANGE_TOKEN_PATH;
        $body = ['code' => $code];

        $response = $this->api->doPost($uri, $body, false);

        return $this->api->generateJwtResponse($response, $response['refreshJwt'] ?? null, null);
    }

    /**
     * Verifies if the OAuth provider is valid.
     *
     * @param  string $oauthProvider The OAuth provider name.
     * @return bool True if provider is valid, false otherwise.
     */
    private function verifyProvider(string $oauthProvider): bool
    {
        if ($oauthProvider === "") {
            return false;
        }
        return true;
    }

    /**
     * Composes the OAuth start URL with query parameters.
     *
     * @param  string $provider  OAuth provider name.
     * @param  string $returnUrl Optional redirect URL.
     * @return string Composed URL with query parameters.
     */
    private function composeStartUrl(string $provider, string $returnUrl): string
    {
        $uri = EndpointsV1::$OAUTH_START_PATH;
        $query = ['provider' => $provider];

        if (!empty($returnUrl)) {
            $query['redirectURL'] = $returnUrl;
        }

        if (!empty($query)) {
            $uri .= '?' . http_build_query($query);
        }

        return $uri;
    }

    /**
     * Composes the request body for OAuth start.
     *
     * @param  \Descope\SDK\LoginOptions|null $loginOptions Optional login options.
     * @return array Request body array.
     */
    private function composeStartBody(?\Descope\SDK\LoginOptions $loginOptions): array
    {
        if ($loginOptions === null) {
            return [];
        }

        $body = [
            'stepup' => $loginOptions->stepup ?? false,
            'mfa' => $loginOptions->mfa ?? false,
        ];

        if ($loginOptions->customClaims !== null) {
            $body['customClaims'] = $loginOptions->customClaims;
        }

        if ($loginOptions->templateOptions !== null) {
            $body['templateOptions'] = $loginOptions->templateOptions;
        }

        return $body;
    }
}
