<?php

namespace Descope\SDK;

use Descope\SDK\API;
use Descope\SDK\Token\Extractor;
use Descope\SDK\Token\Verifier;
use Descope\SDK\Configuration\SDKConfig;
use Descope\SDK\Auth\Password;
use Descope\SDK\Auth\SSO;
use Descope\SDK\Management\Management;
use Descope\SDK\Auth\Management\User;
use Descope\SDK\Auth\Management\Audit;
use Descope\SDK\EndpointsV1;
use Descope\SDK\Management\MgmtV1;

class DescopeSDK
{
    private SDKConfig $config;
    public Password $password;
    public SSO $sso;
    public Management $management;
    public API $api;

    /**
     * Constructor for DescopeSDK class.
     *
     * @param array $config Base configuration options for the SDK.
     */
    public function __construct(array $config)
    {
        if (!isset($config['projectId'])) {
            throw new \InvalidArgumentException('Please add a Descope Project ID to your .ENV file.');
        }

        EndpointsV1::setBaseUrl($config['projectId']);
        EndpointsV2::setBaseUrl($config['projectId']);

        $this->config = new SDKConfig($config);

        $this->api = new API($config['projectId'], $config['managementKey'] ?? '');
        // If OPTIONAL management key was provided in $config
        if (!empty($config['managementKey'])) {
            $this->management = new Management($this->api);
            MgmtV1::setBaseUrl($config['projectId']);
        }

        $this->password = new Password($this->api);
        $this->sso = new SSO($this->api);
    }

     /**
     * Verify if the JWT is valid and not expired.
     *
     * @param string|null $sessionToken The session token to verify.
     * @return bool Verification result.
     * @throws AuthException
     */
    public function verify($sessionToken = null)
    {
        $sessionToken = $sessionToken ?? $_COOKIE[EndpointsV1::SESSION_COOKIE_NAME_NAME] ?? null;

        if (!$sessionToken) {
            throw new \InvalidArgumentException('Session token is required.');
        }

        $verifier = new Verifier($this->config);
        return $verifier->verify($sessionToken);
    }

    /**
     * Refresh session token using the refresh token.
     *
     * @param string|null $refreshToken The refresh token to use.
     * @return array The new session information.
     * @throws AuthException
     */
    public function refreshSession($refreshToken = null)
    {
        $refreshToken = $refreshToken ?? $_COOKIE[EndpointsV1::REFRESH_COOKIE_NAME] ?? null;

        if (!$refreshToken) {
            throw new \InvalidArgumentException('Refresh token is required.');
        }

        $verifier = new Verifier($this->config);
        return $verifier->refreshSession($refreshToken);
    }

    /**
     * Verify and refresh the session using session and refresh tokens.
     *
     * @param string|null $sessionToken The session token.
     * @param string|null $refreshToken The refresh token.
     * @return array The refreshed session information.
     * @throws AuthException
     */
    public function verifyAndRefreshSession($sessionToken = null, $refreshToken = null)
    {
        $sessionToken = $sessionToken ?? $_COOKIE[EndpointsV1::SESSION_COOKIE_NAME] ?? null;
        $refreshToken = $refreshToken ?? $_COOKIE[EndpointsV1::REFRESH_COOKIE_NAME] ?? null;

        if (!$sessionToken || !$refreshToken) {
            throw new \InvalidArgumentException('Session token and refresh token are required.');
        }

        $verifier = new Verifier($this->config);
        return $verifier->verifyAndRefreshSession($sessionToken, $refreshToken);
    }

    /**
     * Get the JWT claims if the token is valid.
     *
     * @param string|null $token The token to extract claims from.
     * @return array The JWT claims.
     * @throws AuthException
     */
    public function getClaims($token = null)
    {
        $token = $token ?? $_COOKIE[EndpointsV1::SESSION_COOKIE_NAME] ?? null;

        if (!$token) {
            throw new \InvalidArgumentException('Token is required.');
        }

        $extractor = new Extractor($this->config);
        return $extractor->getClaims($token);
    }

    /**
     * Retrieve user details using the refresh token.
     *
     * @param string|null $refreshToken The refresh token of the user.
     * @return array The user details.
     * @throws AuthException
     */
    public function getUserDetails(string $refreshToken = null)
    {
        $refreshToken = $refreshToken ?? $_COOKIE[EndpointsV1::REFRESH_COOKIE_NAME] ?? null;

        if (!$refreshToken) {
            throw new \InvalidArgumentException('Refresh token is required.');
        }

        return $this->api->doGet(
            EndpointsV1::$ME_PATH,
            false,
            $refreshToken
        );
    }

    /**
     * Logout a user using the refresh token.
     *
     * @param string|null $refreshToken The refresh token of the user.
     * @return void
     * @throws AuthException
     */
    public function logout(string $refreshToken = null): void
    {
        $refreshToken = $refreshToken ?? $_COOKIE[EndpointsV1::REFRESH_COOKIE_NAME] ?? null;

        if (!$refreshToken) {
            throw new \InvalidArgumentException('Refresh token is required.');
        }

        $this->api->doPost(
            EndpointsV1::$LOGOUT_PATH,
            [],
            false,
            $refreshToken
        );
    }

    /**
     * Logout a user from all devices using the refresh token.
     *
     * @param string|null $refreshToken The refresh token of the user.
     * @return void
     * @throws AuthException
     */
    public function logoutAll(string $refreshToken = null): void
    {
        $refreshToken = $refreshToken ?? $_COOKIE[EndpointsV1::REFRESH_COOKIE_NAME] ?? null;

        if (!$refreshToken) {
            throw new \InvalidArgumentException('Refresh token is required.');
        }

        $this->api->doPost(
            EndpointsV1::LOGOUT_ALL_PATH,
            [],
            false,
            $refreshToken
        );
    }

    /**
     * Get the Password component.
     *
     * @return Password The Password instance.
     */
    public function password(): Password
    {
        return $this->password;
    }

    /**
     * Get the SSO component.
     *
     * @return SSO The SSO instance.
     */
    public function sso(): SSO
    {
        return $this->sso;
    }

    /**
     * Get the Management component.
     *
     * @return Management The Management instance.
     */
    public function management(): Management
    {
        return $this->management;
    }
}
