<?php

namespace phm\HttpWebdriverClient\Http\Client\Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RedirectMiddleware;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use phm\HttpWebdriverClient\Http\Request\UserAgentAwareRequest;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class GuzzleClient
 * @package phm\HttpWebdriverClient\Http\Client\Guzzle
 *
 * @todo handle cookies
 */
class GuzzleClient implements HttpClient
{
    const CLIENT_TYPE = "guzzle";

    private $client;

    private $standardHeaders = [
        'Accept-Encoding' => 'gzip',
        'Connection' => 'keep-alive'
    ];

    private $options = [
        RequestOptions::VERIFY => false,
        RequestOptions::VERSION => 1.1,
        RequestOptions::DECODE_CONTENT => false,
        RequestOptions::ALLOW_REDIRECTS => [
            'track_redirects' => true,
        ]];

    public function __construct($standardHeaders = null, $timeout = 10)
    {
        if ($standardHeaders) {
            $this->standardHeaders = $standardHeaders;
        }

        $this->options = array_merge($this->options, ['headers' => $this->standardHeaders, 'timeout' => $timeout,]);
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
        $responses = $this->sendRequests([$request]);
        return array_pop($responses);
    }

    /**
     * @param RequestInterface[] $requests
     * @return GuzzleResponse[]
     */
    public function sendRequests(array $requests, $failOnError = false)
    {
        $promises = [];
        $stats = [];

        foreach ($requests as $key => $request) {
            if ($request instanceof UserAgentAwareRequest) {
                $requests[$key] = $request->withAddedHeader('User-Agent', $request->getUserAgent());
            }
        }

        $params = ['on_stats' => function (TransferStats $transferStats) use (&$stats) {
            $stats[(string)($transferStats->getRequest()->getUri())]['totalTime'] = $transferStats->getTransferTime();
            $stats[(string)($transferStats->getRequest()->getUri())]['request'] = $transferStats->getRequest();
        }];

        foreach ($requests as $key => $request) {

            $guzzleRequest = new Request(
                $request->getMethod(),
                $request->getUri(),
                $request->getHeaders(),
                $request->getBody()
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
