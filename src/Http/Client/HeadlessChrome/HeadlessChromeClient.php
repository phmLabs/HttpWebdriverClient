<?php

namespace phm\HttpWebdriverClient\Http\Client\HeadlessChrome;

use phm\HttpWebdriverClient\Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;

class HeadlessChromeClient implements HttpClient
{
    const CLIENT_TYPE = "headless_chrome";

    public function sendRequest(RequestInterface $request)
    {
        
    }

    public function sendRequests(array $requests)
    {
        throw new \RuntimeException('This method is not implemented yet. Please use sendRequest().');
    }

    public function getClientType()
    {
        return self::CLIENT_TYPE;
    }
}
