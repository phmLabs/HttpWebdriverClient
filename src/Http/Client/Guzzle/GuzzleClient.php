<?php

namespace phm\HttpWebdriverClient\Http\Client\Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\TransferStats;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleClient implements HttpClient
{
    private $client;

    private $standardHeaders = [];

    public function __construct($standardHeaders = ['Accept-Encoding' => 'gzip', 'Connection' => 'keep-alive'], $timeout = 10)
    {
        $client = new Client(['headers' => $standardHeaders, 'decode_content' => false, 'timeout' => $timeout]);
        $this->standardHeaders = $standardHeaders;

        $this->client = $client;
    }

    /**
     * @param RequestInterface $request
     * @return GuzzleResponse
     */
    public function sendRequest(RequestInterface $request)
    {
        $request = $this->handleCookies($request);
        $response = $this->client->send($this->handleCookies($request));
        return new GuzzleResponse($response);
    }

    private function handleCookies(RequestInterface $request)
    {
        $uri = $request->getUri();

        // @todo use cookie aware interface
        if (method_exists($uri, 'hasCookies')) {
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
        $timings = [];

        $params = ['on_stats' => function (TransferStats $stats) use (&$timings) {
            $timings[(string)($stats->getRequest()->getUri())]['totalTime'] = $stats->getTransferTime();
        }];

        foreach ($requests as $key => $request) {
            $guzzleRequest = new Request(
                $request->getMethod(),
                $request->getUri(),
                $request->getHeaders()
            );

            $promises[$key] = $this->client->sendAsync($guzzleRequest, $params);
        }

        $results = Promise\settle($promises)->wait();

        $responses = [];

        foreach ($results as $key => $result) {
            if ($result['state'] == 'fulfilled') {
                $responses[$key] = $this->createGuzzleResponse(
                    $result['value'],
                    $requests[$key]->getUri(),
                    $timings
                );
            } else {
                /** @var \GuzzleHttp\Exception\ClientException $exception */
                $exception = ($result['reason']);

                if ($exception instanceof ClientException) {
                    $responses[$key] = $this->createGuzzleResponse(
                        $exception->getResponse(),
                        $requests[$key]->getUri(),
                        $timings
                    );
                } else if ($failOnError) {
                    throw $result['reason'];
                }
            }
        }

        return $responses;
    }

    private function createGuzzleResponse(ResponseInterface $response, $uri, $timings)
    {
        $guzzleResponse = new GuzzleResponse($response);
        $guzzleResponse->setUri($uri);
        $guzzleResponse->setDuration($timings[(string)$guzzleResponse->getUri()]['totalTime'] * 1000);

        return $guzzleResponse;
    }
}
