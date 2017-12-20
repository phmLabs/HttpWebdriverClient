<?php

namespace phm\HttpWebdriverClient\Http\Client\Decorator;

use Cache\Adapter\Common\CacheItem;
use phm\HttpWebdriverClient\Http\Client\Guzzle\Response;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use phm\HttpWebdriverClient\Http\Request\CacheAwareRequest;
use phm\HttpWebdriverClient\Http\Response\TimeoutAwareResponse;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CacheDecorator implements HttpClient
{
    private $cacheItemPool;
    private $client;

    private $expiresAfter;

    private $active = true;

    private $cacheOnTimeout = false;

    public function __construct(HttpClient $client, CacheItemPoolInterface $cacheItemPool, $expiresAfter = null)
    {
        if (!$expiresAfter) {
            $this->expiresAfter = new \DateInterval('PT5M');
        } else {
            $this->expiresAfter = $expiresAfter;
        }
        $this->cacheItemPool = $cacheItemPool;

        $this->client = $client;
    }

    public function sendRequest(RequestInterface $request, $forceRefresh = false)
    {
        if ($this->active) {
            $key = $this->getHash($request);

            if (!$this->cacheItemPool->hasItem($key) || $forceRefresh) {
                $response = $this->client->sendRequest($request);
                if (!$response instanceof TimeoutAwareResponse || !$response->isTimeout() || $this->cacheOnTimeout) {
                    $this->cacheResponse($key, $response);
                }
            } else {
                $serializedResponse = $this->cacheItemPool->getItem($key)->get();
                $response = $this->unserializeResponse($serializedResponse);
            }
            return $response;

        } else {
            return $this->client->sendRequest($request);
        }
    }

    public function sendRequests(array $requests, $forceRefresh = false)
    {
        if ($this->active) {
            $responses = array();

            foreach ($requests as $id => $request) {
                $key = $this->getHash($request);
                if ($this->cacheItemPool->hasItem($key)) {
                    $responses[] = $this->unserializeResponse($this->cacheItemPool->getItem($key)->get());
                    unset($requests[$id]);
                }
            }

            if (count($requests) > 0) {
                $newResponses = $this->client->sendRequests($requests);

                foreach ($newResponses as $newResponse) {
                    /** @var Response $newResponse */
                    $key = $this->getHash($newResponse->getRequest());
                    $this->cacheResponse($key, $newResponse);
                }
                $responses = array_merge($responses, $newResponses);
            }

            return $responses;
        } else {
            return $this->client->sendRequests($requests);
        }
    }

    /**
     * @param  boolean $cacheOnTimeOut
     */
    public function setCacheOnTimeout($cacheOnTimeOut)
    {
        $this->cacheOnTimeout = $cacheOnTimeOut;
    }

    public function close()
    {
        if (method_exists($this->client, 'close')) {
            $this->client->close();
        }
    }

    private function getHash(RequestInterface $request)
    {
        if ($request instanceof CacheAwareRequest) {
            return $request->getHash();
        } else {
            $headers = $request->getHeaders();

            // @todo should  be part of the guzzle client
            if (array_key_exists('User-Agent', $headers)) {
                if (strpos($headers['User-Agent'][0], 'GuzzleHttp') === 0) {
                    unset($headers['User-Agent']);
                }
            }
            $identifier = (string)$request->getUri() . json_encode($headers) . $request->getMethod() . $this->client->getClientType();
            $hash = md5($identifier);
            return $hash;
        }
    }

    private function serializeResponse(ResponseInterface $response)
    {
        return ['response' => serialize($response), 'body' => (string)$response->getBody()];
    }

    private function unserializeResponse($serializedResponse)
    {
        $response = unserialize($serializedResponse['response']);
        /** @var ResponseInterface $response */
        $response->setBody($serializedResponse['body']);
        return $response;
    }

    private function cacheResponse($key, ResponseInterface $response)
    {
        $cacheItem = new CacheItem($key);
        $cacheItem->set($this->serializeResponse($response));
        $cacheItem->expiresAfter($this->expiresAfter);
        return $this->cacheItemPool->save($cacheItem);
    }

    public function getClientType()
    {
        return $this->client->getClientType();
    }

    public function setOption($key, $value)
    {
        return $this->client->setOption($key, $value);
    }

    public function getClient()
    {
        if ($this->client instanceof ClientDecorator) {
            return $this->client->getClient();
        } else {
            return $this->client;
        }
    }

    public function deactivateCache()
    {
        $this->active = false;
    }
}
