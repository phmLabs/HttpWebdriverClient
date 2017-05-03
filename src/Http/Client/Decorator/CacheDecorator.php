<?php

namespace phm\HttpWebdriverClient\Http\Client\Decorator;

use Cache\Adapter\Common\CacheItem;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CacheDecorator implements HttpClient
{
    private $cacheItemPool;
    private $client;

    private $expiresAfter;

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

    public function sendRequest(RequestInterface $request)
    {
        $key = $this->getHash($request);

        if ($this->cacheItemPool->hasItem($key)) {
            $serializedResponse = $this->cacheItemPool->getItem($key)->get();
            return $this->unserializeResponse($serializedResponse);
        } else {
            $response = $this->client->sendRequest($request);
            $this->cacheResponse($key, $response);
            return $response;
        }
    }

    public function sendRequests(array $requests)
    {
        $responses = array();

        foreach ($requests as $id => $request) {
            $key = $this->getHash($request);
            if ($this->cacheItemPool->hasItem($key)) {
                $responses[] = $this->unserializeResponse($this->cacheItemPool->getItem($key)->get());
                unset($requests[$id]);
            }
        }

        $newResponses = $this->client->sendRequests($requests);

        foreach ($newResponses as $newResponse) {
            /** @var Response $newResponse */
            $key = $this->getHash($newResponse->getRequest());
            $this->cacheResponse($key, $newResponse);
        }

        $responses = array_merge($responses, $newResponses);

        return $responses;
    }

    public function close()
    {
        if (method_exists($this->client, 'close')) {
            $this->client->close();
        }
    }

    private function getHash(RequestInterface $request)
    {
        return md5((string)$request->getUri() . json_encode($request->getHeaders()) . $request->getMethod());
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
}
