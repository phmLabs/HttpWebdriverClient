<?php

namespace phm\HttpWebdriverClient\Http\Client\Guzzle;

use Ivory\HttpAdapter\Guzzle6HttpAdapter;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
use Ivory\HttpAdapter\CurlHttpAdapter;
use Ivory\HttpAdapter\Event\Subscriber\RedirectSubscriber;
use Ivory\HttpAdapter\Event\Subscriber\RetrySubscriber;
use Ivory\HttpAdapter\EventDispatcherHttpAdapter;
use Symfony\Component\EventDispatcher\EventDispatcher;
use whm\Crawler\Http\RequestFactory;

class GuzzleClient implements HttpClient
{
    private $client;

    public function __construct()
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new RedirectSubscriber());
        $eventDispatcher->addSubscriber(new RetrySubscriber());
        $guessedAdapter = new CurlHttpAdapter();

        RequestFactory::addStandardHeader('Accept-Encoding', 'gzip');
        RequestFactory::addStandardHeader('Connection', 'keep-alive');

        $adapter = new EventDispatcherHttpAdapter($guessedAdapter, $eventDispatcher);
        $adapter->getConfiguration()->setTimeout(30);
        $adapter->getConfiguration()->setMessageFactory(new MessageFactory());

        $this->client = $adapter;
    }

    public function sendRequest(RequestInterface $request)
    {
        return $this->client->sendRequest($this->handleCookies($request));
    }

    private function handleCookies(RequestInterface $request)
    {
        $uri = $request->getUri();
        if (method_exists($uri, 'hasCookies')) {
            if ($uri->hasCookies()) {
                $request = $request->withAddedHeader('Cookie', $uri->getCookieString());
            }
        }

        return $request;
    }

    public function sendRequests(array $requests)
    {
        foreach ($requests as $key => $request) {
            $requests[$key] = $this->handleCookies($request);
        }
        return $this->client->sendRequests($requests);
    }
}
