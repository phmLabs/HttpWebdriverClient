<?php

namespace phm\HttpWebdriverClient\Http\Client;

use Psr\Http\Message\RequestInterface;

class FallbackClient implements HttpClient
{
    /**
     * @var HttpClient[]
     */
    private $clients = [];

    public function __construct(HttpClient $client)
    {
        $this->clients[] = $client;
    }

    public function addFallbackClient(HttpClient $client)
    {
        $this->clients[] = $client;
    }

    public function sendRequest(RequestInterface $request)
    {
        $exceptions = [];

        foreach ($this->clients as $client) {
            try {
                return $client->sendRequest($request);
            } catch (\Exception $e) {
                $exceptions[] = $e;
            }
        }

        throw new \Exception('FallbackClient Error: every client failed to send request.');
    }

    public function sendRequests(array $requests)
    {
        $exceptions = [];

        foreach ($this->clients as $client) {
            try {
                return $client->sendRequests($requests);
            } catch (\Exception $e) {
                $exceptions[] = $e;
            }
        }

        throw new \Exception('FallbackClient Error: every client failed to send requests.');
    }

    public function getClientType()
    {
        $clientType = 'FALLBACK_CLIENT';
        foreach ($this->clients as $client) {
            $clientType .= '_' . $client->getClientType();
        }
        return $clientType;
    }

    public function setOption($key, $value)
    {
        foreach ($this->clients as $client) {
            $client->setOption($key, $value);
        }
    }

    public function close()
    {
        foreach ($this->clients as $client) {
            $client->close();
        }
    }
}