<?php
namespace Descope\SDK;

require __DIR__ . '/../../vendor/autoload.php';

use Descope\SDK\Token\Extractor;
use Descope\SDK\Token\Verifier;
use Descope\SDK\Configuration\SDKConfig;

class DescopeSDK
{
    private SDKConfig $config;

    /**
     * Constructor for DescopeSDK class.
     *
     * @param SDKConfig $config Base configuration options for the SDK.
     *
     */
    public function __construct(array $config)
    {
        $this->config = new SDKConfig($config);
    }

    /**
     * Verify if the JWT is valid and not expired.
     *
     */
    public function verify($token)
    {
        $verifier = new Verifier($this->config);
        return $verifier->verify($token);
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
}
