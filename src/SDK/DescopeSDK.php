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

class DescopeSDK
{
    private SDKConfig $config;
    public Password $password;
    public SSO $sso;
    public Management $management;
    public API $api;

    public string $baseUrl;

    /**
     * Constructor for DescopeSDK class.
     *
     * @param SDKConfig $config Base configuration options for the SDK.
     */
    public function __construct(array $config)
    {
        if (!isset($config['projectId'])) {
            throw new \InvalidArgumentException('Please add a Descope Project ID to your .ENV file.');
        }

        $this->config = new SDKConfig($config);

        $this->api = new API($config['projectId'], $config['managementKey'] ?? '');
        // If OPTIONAL management key was provided in $config
        if (!empty($config['managementKey'])) {
            $this->management = new Management($this->api);
        }

        $this->password = new Password($this->api);
        $this->sso = new SSO($this->api);
    }

    /**
     * Verify if the JWT is valid and not expired.
     */
    public function verify($sessionToken)
    {
        $verifier = new Verifier($this->config);
        return $verifier->verify($sessionToken);
    }

    /**
     * Refresh session token with refresh token.
     */
    public function refreshSession($refreshToken)
    {
        $verifier = new Verifier($this->config);
        return $verifier->refreshSession($refreshToken);
    }

    /**
     * Verify if the JWT is valid and not expired.
     */
    public function verifyAndRefreshSession($sessionToken, $refreshToken)
    {
        $verifier = new Verifier($this->config);
        return $verifier->verifyAndRefreshSession($sessionToken, $refreshToken);
    }

    /**
     * Returns the JWT claims, if the JWT is valid.
     */
    public function getClaims($token)
    {
        $extractor = new Extractor($this->config);
        return $extractor->getClaims($token);
    }

    /**
     * Returns the user details, using the refresh token.
     *
     * @param  string $refreshToken The refresh token of the user.
     * @return void
     * @throws AuthException if the logout operation fails.
     */
    public function getUserDetails(string $refreshToken)
    {
        $this->api->doPost(
            EndpointsV1::ME_PATH,
            [],
            false,
            $refreshToken
        );
    }

    /**
     * Logout a user from all devices.
     *
     * @param  string $refreshToken The refresh token of the user.
     * @return void
     * @throws AuthException if the logout operation fails.
     */
    public function logout(string $refreshToken): void
    {
        $this->api->doPost(
            EndpointsV1::LOGOUT_PATH,
            [],
            false,
            $refreshToken
        );
    }

    /**
     * Logout a user from all devices.
     *
     * @param  string $refreshToken The refresh token of the user.
     * @return void
     * @throws AuthException if the logout operation fails.
     */
    public function logoutAll(string $refreshToken): void
    {
        $this->api->doPost(
            EndpointsV1::LOGOUT_ALL_PATH,
            [],
            false,
            $refreshToken
        );
    }

    /**
     * Get the Password component.
     */
    public function password(): Password
    {
        return $this->password;
    }

    /**
     * Get the SSO component.
     */
    public function sso(): SSO
    {
        return $this->sso;
    }

    /**
     * Get the Management component.
     */
    public function management(): Management
    {
        return $this->management;
    }
}
