<?php

namespace Descope\SDK\Configuration;

require __DIR__ . '/../../../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

final class SDKConfig
{
    public $client;
    public $projectId;
    public $jwkSets;

    public function __construct(array $config)
    {
        $this->client = new Client();
        if (isset($config['projectId'])) {
            $this->projectId = $config['projectId'];
        } else {
            throw new \InvalidArgumentException('Please add a Descope Project ID to your .ENV file.');
        }

        $this->jwkSets = $this->getJWKSets();
    }

    /**
     * Gets the current JWK KeySet that will be needed to validate the JWT
     *
     */
    private function getJWKSets()
    {
        try {
            // Fetch JWK public key from Descope API
            $url = 'https://api.descope.com/v2/keys/' . $this->projectId;
            $res = $this->client->request('GET', $url);
            $jwkSets = json_decode($res->getBody(), true);
            return $jwkSets;
        } catch (RequestException $re) {
            return $re;
        }
    }
}
