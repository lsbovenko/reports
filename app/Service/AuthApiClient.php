<?php

namespace App\Service;

use GuzzleHttp\Client;

/**
 * Class AuthApiClient
 * @package App\Service
 */
class AuthApiClient
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * AuthApiClient constructor.
     * @param Client $httpClient
     */
    function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->setApiKey();
    }

    /**
     * @param string $email
     * @return bool|mixed
     * @throws \Exception
     */
    public function getUser(string $email)
    {
        $endpoint = 'users/' . $email;

        try {
            $response = $this->sendRequest($endpoint, 'GET');
            if ($response->getStatusCode() == 200) {
                $user = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
            }
        } catch (\Exception $e) {
            throw new \Exception('Error code ' . $e->getCode());
        }

        return $user ?? false;
    }

    /**
     * @param array $query
     * @return mixed
     * @throws \Exception
     */
    public function getUsers(array $query = [])
    {
        $endpoint = 'users';
        $options = ['query' => $query];

        try {
            $response = $this->sendRequest($endpoint, 'GET', $options);
            if ($response->getStatusCode() == 200) {
                $users = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
            }
        } catch (\Exception $e) {
            throw new \Exception('Error code ' . $e->getCode());
        }

        return $users;
    }


    /**
     * @param string $endpoint
     * @param string $method
     * @param array $options
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    protected function sendRequest(string $endpoint, string $method, array $options = [])
    {
        $url = $this->getApiUrl() . $endpoint;
        $options['headers'] = $this->getRequestHeaders();

        return $this->httpClient->request($method, $url, $options);
    }

    /**
     * @return array
     */
    protected function getRequestHeaders(): array
    {
        return [
            'X-Ikantam-API-Key' => $this->apiKey
        ];
    }

    /**
     * @return $this
     */
    protected function setApiKey()
    {
        $apiKey = config('app.auth_api_key');
        if (!$apiKey) {
            throw new \Exception('auth_api_key not found');
        }

        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return string
     */
    protected function getApiUrl(): string
    {
        return config('app.auth_url') . '/api/v1/';
    }
}