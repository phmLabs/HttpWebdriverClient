<?php

namespace phm\HttpWebdriverClient\Http\Decorator;

use Cache\Adapter\Common\CacheItem;
use phm\HttpWebdriverClient\Http\HttpClient;
use phm\HttpWebdriverClient\Http\Response;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;

class CacheDecorator implements HttpClient
{
    private $cacheItemPool;
    private $client;

    private function getHash(RequestInterface $request)
    {
        return md5((string)$request->getUri() . json_encode($request->getHeaders()) . $request->getMethod());
    }

    private function serialzeResponse(Response $response)
    {
        return serialize($response);
    }

    private function unserialseResponse($serialzedResponse)
    {
        return unserialize($serialzedResponse);
    }

    public function __construct(HttpClient $client, CacheItemPoolInterface $cacheItemPool)
    {
        $this->cacheItemPool = $cacheItemPool;
        $this->client = $client;
    }

    public function sendRequest(RequestInterface $request)
    {
        $key = $this->getHash($request);

        if ($this->cacheItemPool->hasItem($key)) {
            $serializedResponse = $this->cacheItemPool->getItem($key)->get();
            return $this->unserialseResponse($serializedResponse);
        } else {
            $response = $this->client->sendRequest($request);
            $cacheItem = new CacheItem($key);
            $cacheItem->set($this->serialzeResponse($response));
            $cacheItem->expiresAfter(new \DateInterval('P5M'));
            $this->cacheItemPool->save($cacheItem);
            return $response;
        }
    }
}
