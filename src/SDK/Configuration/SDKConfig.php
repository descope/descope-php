<?php

namespace Descope\SDK\Configuration;

require '../../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

final class SDKConfig {
    private $client;
    private $projectId;
    private $jwkSets;

    public function __construct($projectId)
    {
        $this->client = new Client();
        $this->projectId = $projectId;

        $this->$jwkSets = getJWKSets();
    }

    /**
     * Gets the current JWK KeySet that will be needed to validate the JWT
     *
     */
    private function getJWKSets()
    {
        try {
            // Fetch JWK public key from Descope API
            $url = 'https://api.descope.com/v2/keys/' . $projectId;
            $res = $client->request('GET', $url);
            $jwkSets = json_decode($res->getBody(), true);
            return $jwkSets;
        } catch (RequestException $re) {
            return $re;
        }
    }
}