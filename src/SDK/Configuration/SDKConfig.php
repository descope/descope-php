<?php

namespace Descope\SDK\Configuration;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Descope\SDK\EndpointsV2;
use Descope\SDK\API;

final class SDKConfig
{
    public $client;
    public $projectId;
    public $managementKey;
    public $jwkSets;

    public function __construct(array $config)
    {
        $this->client = new Client();
        $this->projectId = $config['projectId'];
        $this->managementKey = $config['managementKey'] ?? '';
        
        EndpointsV2::setBaseUrl($config['projectId']);
        $this->jwkSets = $this->getJWKSets();
    }

    /**
     * Gets the current JWK KeySet that will be needed to validate the JWT
     */
    private function getJWKSets()
    {
        try {
            // Fetch JWK public key from Descope API
            $response = $this->api->doGet(EndpointsV2::getPublicKeyPath(), false);
            return json_decode($response->getBody(), true);
        } catch (RequestException $re) {
            return $re;
        }
    }
}
