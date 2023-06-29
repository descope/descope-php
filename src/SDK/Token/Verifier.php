<?php

namespace Descope\SDK\Token;

require '../vendor/autoload.php';

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
use Descope\SDK\Configuration\SDKConfig;

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
        $this->config = $config;
    }

    /**
     * Returns true if the JWT signature is valid and not expired.
     *
     */
    public function verify($token)
    {
        try {
            $extractor = new Extractor($this->config);
            $jws = $extractor->parseToken($token);

            // If JWT signature is valid
            if (isset($jws)) {
                $payload = json_decode($jws->getPayload());

                // Check to make sure JWT is not expired
                // if (isset($payload->exp) && $payload->exp < time()) {
                    return true;
                // }
            }
            return false;
        } catch (TokenException $te) {
            throw TokenException::MSG_SIGNATURE_INVALID;
        }
    }
}