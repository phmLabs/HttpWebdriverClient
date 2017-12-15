<?php

namespace phm\HttpWebdriverClient\Http\Client\Decorator;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

class LoggerDecorator implements HttpClient
{
    const DEFAULT_LOG_FILE = '/tmp/log/leankoala.log';

    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(HttpClient $client, LoggerInterface $logger = null)
    {
        $this->client = $client;

        if ($logger) {
            $this->logger = $logger;
        } else {
            $this->logger = new Logger('Client::' . $this->client->getClientType() . '::' . md5(microtime()));
            $this->logger->pushHandler(new StreamHandler(self::DEFAULT_LOG_FILE, Logger::INFO));
        }

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

    public function setOption($key, $value)
    {
        return $this->client->setOption($key, $value);
    }

}
