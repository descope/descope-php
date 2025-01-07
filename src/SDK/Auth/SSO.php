<?php

declare(strict_types=1);

namespace Descope\SDK\Auth;

use Descope\SDK\Exception\AuthException;
use Descope\SDK\EndpointsV1;
use Descope\SDK\API;

class SSO
{
    /**
     * @var API The API object for making authenticated requests.
     */
    private $api;

    /**
     * Constructor for SSO class.
     *
     * @param API $api API object for making authenticated requests.
     */
    public function __construct(API $api)
    {
        $this->api = $api;
    }

    /**
     * SSO sign-in request.
     *
     * @param  string|null $tenant       Tenant identifier.
     * @param  string|null $redirectUrl  URL to redirect after authentication.
     * @param  string|null $prompt       Prompt parameter.
     * @param  bool        $stepup       Whether to perform step-up authentication.
     * @param  bool        $mfa          Whether to enforce MFA.
     * @param  array       $customClaims Custom claims to include in the token.
     * @param  string|null $ssoAppId     SSO application identifier.
     * @return array Response array.
     * @throws AuthException
     */
    public function signIn(?string $tenant = null, ?string $redirectUrl = null, ?string $prompt = null, bool $stepup = false, bool $mfa = false, array $customClaims = [], ?string $ssoAppId = null): array
    {
        $this->validateTenant($tenant);
        $this->validateRedirectUrl($redirectUrl);
        $uri = $this->composeSignInUrl($tenant, $redirectUrl, $prompt);

        $requestParams = [
            'stepup' => $stepup,
            'mfa' => $mfa,
            'customClaims' => $customClaims,
        ];
        if ($ssoAppId !== null) {
            $requestParams['ssoAppId'] = $ssoAppId;
        }

        $response = $this->api->doPost($uri, $requestParams);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * Exchanges SSO code for authentication.
     *
     * @param  string|null $code The exchange code.
     * @return array Response array.
     */
    public function exchangeToken(?string $code = null): array
    {
        $uri = EndpointsV1::$SSO_EXCHANGE_TOKEN_PATH;
        $body = ['code' => $code];

        $response = $this->api->doPost($uri, $body);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * Composes the SSO sign-in URL.
     *
     * @param  string|null $tenant      Tenant identifier.
     * @param  string|null $redirectUrl Redirect URL.
     * @param  string|null $prompt      Prompt parameter.
     * @return string Composed URL.
     */
    private function composeSignInUrl(?string $tenant, ?string $redirectUrl, ?string $prompt): string
    {
        $uri = EndpointsV1::$AUTH_SSO_START_PATH;
        $query = [];
        if ($tenant !== null) {
            $query['tenant'] = urlencode($tenant);
        }
        if ($redirectUrl !== null) {
            $query['redirectUrl'] = urlencode($redirectUrl);
        }
        if ($prompt !== null) {
            $query['prompt'] = urlencode($prompt);
        }
        if (!empty($query)) {
            $uri .= '?' . http_build_query($query);
        }
        return $uri;
    }

    /**
     * Validates the tenant parameter.
     *
     * @param  string|null $tenant The tenant identifier.
     * @throws AuthException
     */
    private function validateTenant(?string $tenant): void
    {
        if ($tenant === null || $tenant === '') {
            throw new AuthException(400, 'invalid argument', 'tenant cannot be empty');
        }
    }

    /**
     * Validates the redirect URL parameter.
     *
     * @param  string|null $redirectUrl The redirect URL.
     * @throws AuthException
     */
    private function validateRedirectUrl(?string $redirectUrl): void
    {
        if ($redirectUrl === null || $redirectUrl === '') {
            throw new AuthException(400, 'invalid argument', 'redirectUrl cannot be empty');
        }
    }
}
