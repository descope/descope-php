<?php

namespace Descope\SDK\Token;

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
use Descope\SDK\EndpointsV1;
use Descope\SDK\API;

final class Verifier
{
    private SDKConfig $config;
    private API $api;

    /**
     * Constructor for Verifier class.
     *
     * @param SDKConfig $config Base configuration options for the SDK.
     */
    public function __construct(SDKConfig $config, API $api)
    {
        $this->config = $config;
        $this->api = $api;
    }

    /**
     * Returns true if the JWT signature is valid and not expired.
     *
     * @param  string $sessionToken The session token.
     * @return boolean Token signature is valid and not expired.
     * @throws AuthException If the refresh operation fails.
     */
    public function verify($sessionToken, ?string $audience = null)
    {
        try {
            $extractor = new Extractor($this->config);
            $jws = $extractor->parseToken($sessionToken);

            // If JWT signature is valid
            if (isset($jws)) {
                $payload = json_decode($jws->getPayload());

                // Check to make sure JWT is not expired
                if (isset($payload->exp) && time() < $payload->exp) {
                    if ($audience && (!isset($payload->aud) || $payload->aud !== $audience)) {
                        return false;
                    }
                    return true;
                }
            }
            return false;
        } catch (TokenException $te) {
            throw TokenException::MSG_SIGNATURE_INVALID;
        }
    }
}
