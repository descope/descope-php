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
use Descope\SDK\Exception\TokenException;
use Descope\SDK\Configuration\SDKConfig;

final class Extractor {
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
     * Return an array representing the Token's claims.
     *
     * @return array<string,array<int|string>|int|string>
     */
    public function getClaims($sessionToken): array
    {
        $jws = $this->parseToken($sessionToken);
        $claims = json_decode($jws->getPayload(), true);

        if (!is_array($claims)) {
            return [];
        }
        return $claims;
    }

    /**
     * Return all user information using /auth/me API endpoint.
     *
     * @return json
     */
    public function getUserDetails($refreshToken) {
        $client = $this->config->client;
        try {
            $url = 'https://api.descope.com/v1/auth/me';
            $header = 'Bearer ' . $refreshToken;
            $res = $client->request('GET', $url, [
                'headers' => ['Authorization' => $header]
            ]);
            $jwkSets = json_decode($res->getBody(), true);
            return $jwkSets;
        } catch (RequestException $re) {
            return $re->getMessage();
        }
    }

    /**
     * Parse a JWT string, returning the JWS Object with all of the claims if valid signature. 
     *
     * @throws TokenException if signature verification fails or parsing fails.
     */
    public function parseToken($sessionToken)
    {
        try {
            $jwkSets = $this->config->jwkSets;
            $jwkSet = JWKSet::createFromKeyData($jwkSets);

            $jwsVerifier = new JWSVerifier(
                new AlgorithmManager([
                    new RS256(),
                ])
            );
                
            $serializerManager = new JWSSerializerManager([
                new CompactSerializer(),
            ]);
    
            $jws = $serializerManager->unserialize($sessionToken);

            $isVerified = $jwsVerifier->verifyWithKeySet($jws, $jwkSet, 0);
            if ($isVerified) {
                return $jws;
            } else {
                throw TokenException::MSG_SIGNATURE_INVALID;
            }
        } catch (Exception $e) {
            throw TokenException::MSG_COULD_NOT_PARSE;
        }
    }
}
