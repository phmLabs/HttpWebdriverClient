<?php

namespace phm\HttpWebdriverClient\Http\Client\Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RedirectMiddleware;
use GuzzleHttp\TransferStats;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use whm\Html\CookieAware;

class GuzzleClient implements HttpClient
{
    const CLIENT_TYPE = "guzzle";

    private $client;

    private $standardHeaders = [];

    private $options = [
        'verify' => false,
        'decode_content' => false,
        'allow_redirects' => [
            'track_redirects' => true,
        ]];

    public function __construct($standardHeaders = ['Accept-Encoding' => 'gzip', 'Connection' => 'keep-alive'], $timeout = 10)
    {
        $this->options = array_merge($this->options, ['headers' => $standardHeaders, 'timeout' => $timeout,]);
        $this->standardHeaders = $standardHeaders;
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        unset($this->client);
    }

    private function getClient()
    {
        if (!isset($this->client)) {
            $this->client = new Client($this->options);
        }

        return $this->client;
    }

    /**
     * @param RequestInterface $request
     * @return GuzzleResponse
     */
    public function sendRequest(RequestInterface $request)
    {
        $request = $this->handleCookies($request);
        $response = $this->getClient()->send($this->handleCookies($request));
        return new GuzzleResponse($response, $request);
    }

    private function handleCookies(RequestInterface $request)
    {
        $uri = $request->getUri();

        if ($uri instanceof CookieAware) {
            if ($uri->hasCookies()) {
                $request = $request->withAddedHeader('Cookie', $uri->getCookieString());
            }
        }

        return $request;
    }

    /**
     * @param Request $requests
     * @return GuzzleResponse[]
     */
    public function sendRequests(array $requests, $failOnError = false)
    {
        foreach ($requests as $key => $request) {
            $requests[$key] = $this->handleCookies($request);
        }

        $promises = [];
        $stats = [];

        $params = ['on_stats' => function (TransferStats $transferStats) use (&$stats) {
            $stats[(string)($transferStats->getRequest()->getUri())]['totalTime'] = $transferStats->getTransferTime();
            $stats[(string)($transferStats->getRequest()->getUri())]['request'] = $transferStats->getRequest();
        }];

        foreach ($requests as $key => $request) {
            $guzzleRequest = new Request(
                $request->getMethod(),
                $request->getUri(),
                $request->getHeaders()
            );

            $promises[$key] = $this->getClient()->sendAsync($guzzleRequest, $params);
        }

        $results = Promise\settle($promises)->wait();

        $responses = [];

        foreach ($results as $key => $result) {
            if ($result['state'] == 'fulfilled') {
                $responses[$key] = $this->createGuzzleResponse(
                    $result['value'],
                    $requests[$key]->getUri(),
                    $stats
                );
            } else {
                /** @var \GuzzleHttp\Exception\ClientException $exception */
                $exception = ($result['reason']);

                if ($exception instanceof ClientException) {
                    $responses[$key] = $this->createGuzzleResponse(
                        $exception->getResponse(),
                        $requests[$key]->getUri(),
                        $stats
                    );
                } else if ($failOnError) {
                    throw $result['reason'];
                }
            }
        }

        return $responses;
    }

    private function createGuzzleResponse(ResponseInterface $response, $uri, $stats)
    {
        $guzzleResponse = new GuzzleResponse($response);

        $guzzleResponse->setUri($uri);
        $guzzleResponse->setDuration($stats[(string)$guzzleResponse->getUri()]['totalTime'] * 1000);
        $guzzleResponse->setRequest($stats[(string)$guzzleResponse->getUri()]['request']);

        if ($response->hasHeader(RedirectMiddleware::HISTORY_HEADER)) {
            $redirectHeader = $response->getHeader(RedirectMiddleware::HISTORY_HEADER);
            $location = array_pop($redirectHeader);
            $effectiveUri = new Uri($location);
            $guzzleResponse->setEffectiveUri($effectiveUri);
        }

        return $guzzleResponse;
    }

    public function getClientType()
    {
        return self::CLIENT_TYPE;
    }
}
