<?php

require 'vendor/autoload.php';

use Descope\SDK\Token\Extractor;
use Descope\SDK\Token\Verifier;
use Descope\SDK\Configuration\SDKConfig;

class DescopeSDK {
    private SDKConfig $config;

    /**
     * Constructor for DescopeSDK class.
     *
     * @param SDKConfig $config Base configuration options for the SDK.
     *
     */
    public function __construct($projectId)
    {
        $this->$config = new SDKConfig([
            'projectId' => $projectId
        ]);
    }

    /**
     * Verify if the JWT is valid.
     *
     */
    public function verify($token) 
    {   
        $verifier = new Verifier($config);
        return $verifier->verify($token);
    }

    /**
     * Verify if the JWT is expired.
     *
     */
    public function tokenExpired($token) 
    {
        $verifier = new Verifier($config);
        return $verifier->tokenExpired($token);
    }

    /**
     * Returns the JWT claims, if the JWT is valid.
     *
     */
    public function getClaims($token)
    {
        $extractor = new Extractor($config);
        return $verifier->getClaims($token);
    }

    /**
     * TODO: Returns the user details, using the refresh token. 
     *
     */
    public function getUser($refreshToken) {
        $extractor = new Extractor($config);
        return $verifier->getUserDetails($refreshToken);
    }
}