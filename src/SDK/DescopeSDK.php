<?php

namespace Descope\SDK;

use Descope\SDK\Token\Extractor;
use Descope\SDK\Token\Verifier;
use Descope\SDK\Configuration\SDKConfig;
use Descope\SDK\Auth\Password;
use Descope\SDK\Auth\SSO;
use Descope\SDK\Auth\Management\User;
use Descope\SDK\Auth\Management\Audit;

class DescopeSDK
{
    private SDKConfig $config;
    private Password $password;
    private SSO $sso;
    private Management $management;

    /**
     * Constructor for DescopeSDK class.
     *
     * @param SDKConfig $config Base configuration options for the SDK.
     *
     */
    public function __construct(array $config)
    {
        $this->config = new SDKConfig($config);
        $auth = new API($this->config);
        $this->password = new Password($auth);
        $this->sso = new SSO($auth);
        $this->management = new Management($auth);
    }

    /**
     * Verify if the JWT is valid and not expired.
     *
     */
    public function verify($sessionToken)
    {
        $verifier = new Verifier($this->config);
        return $verifier->verify($sessionToken);
    }

    /**
     * Refresh session token with refresh token.
     *
     */
    public function refreshSession($refreshToken)
    {
        $verifier = new Verifier($this->config);
        return $verifier->refreshSession($refreshToken);
    }

    /**
     * Verify if the JWT is valid and not expired.
     *
     */
    public function verifyAndRefreshSession($sessionToken, $refreshToken)
    {
        $verifier = new Verifier($this->config);
        return $verifier->verifyAndRefreshSession($sessionToken, $refreshToken);
    }

    /**
     * Returns the JWT claims, if the JWT is valid.
     *
     */
    public function getClaims($token)
    {
        $extractor = new Extractor($this->config);
        return $extractor->getClaims($token);
    }

    /**
     * TODO: Returns the user details, using the refresh token.
     *
     */
    public function getUserDetails($refreshToken)
    {
        $extractor = new Extractor($this->config);
        return $extractor->getUserDetails($refreshToken);
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
