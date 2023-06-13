<?php

namespace Descope\SDK\Token;

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWKSet;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Descope\SDK\Token\Extractor;

final class Verifier {
    private SDKConfig $config;

    /**
     * Constructor for Verifier class.
     *
     * @param SDKConfig $config   Base configuration options for the SDK.
     *
     */
    public function __construct($config)
    {
        $this->$config = $config;
    }

    /**
     * Returns true or false if the token signature is valid.
     *
     */
    public function verify($token)
    {
        try {
            $extractor = new Extractor($config);
            $jws = $extractor->parseToken($token);
            return true;
        } catch (TokenException $te) {
            return false;
        }
    }

    /**
     * Validate if JWT is expired. Returns true if expired, false if not.
     *
     */
    public function tokenExpired($token) 
    {
        try {
            $extractor = new Extractor($config);
            $jws = $extractor->parseToken($token);

            // If JWT signature is valid
            if (isset($jws)) {
                $payload = JsonConverter::decode($jws->getPayload());
    
                if (isset($payload->exp) && $payload->exp < time()) {
                    return true;
                } else {
                    return false;
                }
            }
        } catch (TokenException $te) {
            return true;
        }
    }
}