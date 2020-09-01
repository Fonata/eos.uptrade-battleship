<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Imbo\BehatApiExtension\Context\ApiContext;
use Psr\Http\Message\ResponseInterface;

class ApiExtensionFeatureContext extends ApiContext
{
    /**
     * Log in as admin
     *
     * @param string $email
     * @return self
     *
     * @Given I am logged in as :email
     */
    public function logIn(string $email)
    {
        try {
            $cookie = $this->getCookie($email);
        } catch (ClientException $e) {
            $body = $e->getResponse()->getBody();
            if (!str_contains($body, 'Email could not be found.')) {
                throw $e;
            }// Create the user that was requested
            $json = [
                'email' => $email,
                'name' => strtok($email, '@'),
                'surname' => 'Müller',
                'roles' => ['ROLE_USER'],
                'password' => 'demo'

            ];
            $this->sendPost('/api/users', $json, $this->getCookie('admin@eos-uptrade.de'));
            return $this->logIn($email);
        }

        $this->request = $this->request->withHeader('Cookie', $cookie);

        return $this;
    }

    /**
     * @param string $title
     * @return self
     *
     * @Given the game :title exists
     */
    public function gameExists($title)
    {
        // Create the game that was requested
        $json = [
            'title' => $title,
        ];
        try {
            $this->sendPost('/api/games', $json, $this->getCookie('admin@eos-uptrade.de'));
        } catch (ServerException $e) {
            var_dump((string)$e->getResponse()->getBody());
        }

        return $this;
    }

    private function getCookie(string $email): string
    {
        $json = [
            "email" => $email,
            "password" => "demo"
        ];
        $cookies = $this
            ->sendPost('/login', $json)
            ->getHeader('Set-Cookie');
        return reset($cookies);
    }

    /**
     * @param string $endpointPath
     * @param array $json
     * @param string $cookie
     * @return ResponseInterface
     * @throws GuzzleException
     */
    private function sendPost(string $endpointPath, array $json, string $cookie = ''): ResponseInterface
    {
        $options = [
            'json' => $json,
            'headers' => [
                'Content-Type' => 'application/json',
                'Cookie' => $cookie
            ]
        ];

        $baseUri = $this->client->getConfig('base_uri');
        $client = new Client();
        return $client->post($baseUri . $endpointPath, $options);
    }
}
