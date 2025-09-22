<?php

namespace Descope\SDK\Token;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Descope\SDK\Exception\TokenException;
use GuzzleHttp\Psr7\Request;
use Descope\SDK\Token\Extractor;
use Descope\SDK\Configuration\SDKConfig;
use Descope\SDK\EndpointsV1;
use Descope\SDK\API;

final class Verifier
{
    private SDKConfig $config;
    private API $api;
    private Extractor $extractor;

    /**
     * Constructor for Verifier class.
     *
     * @param SDKConfig $config Base configuration options for the SDK.
     */
    public function __construct(SDKConfig $config, API $api)
    {
        $this->config = $config;
        $this->api = $api;
        $this->extractor = new Extractor($this->config);
    }

    /**
     * Returns true if the JWT signature is valid and not expired.
     *
     * @param  string $sessionToken The session token.
     * @return boolean Token signature is valid and not expired.
     * @throws AuthException If the refresh operation fails.
     */
    public function verify(string $sessionToken, ?string $audience = null): bool
    {
        try {
            // First validate the token signature
            $validatedToken = $this->extractor->validateJWT($sessionToken);
            if (!$validatedToken) {
                throw new TokenException('Invalid token signature');
            }
            
            // Verify expiration
            if (isset($validatedToken['exp']) && time() > $validatedToken['exp']) {
                throw new TokenException('Token has expired');
            }

            // Verify audience if provided
            if ($audience !== null) {
                if (!isset($validatedToken['aud'])) {
                    throw new TokenException('Token is missing audience claim');
                }
                
                // Handle both string and array audience claims
                $tokenAudience = $validatedToken['aud'];
                if (is_array($tokenAudience)) {
                    if (!in_array($audience, $tokenAudience, true)) {
                        throw new TokenException('Token audience does not match expected value');
                    }
                } else {
                    if ($tokenAudience !== $audience) {
                        throw new TokenException('Token audience does not match expected value');
                    }
                }
            }

            // All validations passed
            return true;
        } catch (TokenException $e) {
            // You might want to throw a specific error or return false depending on your needs
            throw new TokenException('Token validation failed: ' . $e->getMessage());
        }
    }
}
