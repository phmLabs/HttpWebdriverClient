<?php

namespace phm\HttpWebdriverClient\Http\Client\HeadlessChrome;

use phm\HttpWebdriverClient\Http\Client\Chrome\ChromeResponse;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;

class HeadlessChromeClient implements HttpClient
{
    const CLIENT_TYPE = "headless_chrome";

    public function sendRequest(RequestInterface $request)
    {
        exec('node ' . __DIR__ . '/Puppeteer/puppeteer.js', $output, $return);
        var_dump($output);
        return new HeadlessChromeResponse(200, "", $request, [], [], $request->getUri());
    }

    public function sendRequests(array $requests)
    {
        throw new \RuntimeException('This method is not implemented yet. Please use sendRequest().');
    }

    public function getClientType()
    {
        return self::CLIENT_TYPE;
    }

    public function close()
    {
    }
}
