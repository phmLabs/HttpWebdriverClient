<?php

namespace phm\HttpWebdriverClient\Http\Client\Decorator;

use phm\HttpWebdriverClient\Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

class LoggerDecorator implements HttpClient
{
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(HttpClient $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;

        $this->logger->info('client::__construct - clientType: ' . $this->getClientType());
    }

    public function sendRequest(RequestInterface $request)
    {
        $this->logger->info('client::sendRequest - url: ' . (string)$request->getUri());
        return $this->client->sendRequest($request);
    }

    public function sendRequests(array $requests)
    {
        $urls = "";
        foreach ($requests as $request) {
            $urls .= (string)$request->getUri();
        }
        $this->logger->info('client::sendRequests - url: ' . $urls);

        return $this->client->sendRequests($requests);
    }

    public function close()
    {
        $this->logger->info('client::close()');
        if (method_exists($this->client, 'close')) {
            $this->client->close();
        }
    }

    public function getClientType()
    {
        return $this->client->getClientType();
    }
}
