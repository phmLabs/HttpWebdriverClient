<?php

namespace phm\HttpWebdriverClient\Http\Client\HeadlessChrome;

use phm\HttpWebdriverClient\Http\Client\HttpClient;
use phm\HttpWebdriverClient\Http\Client\TimeOutException;
use Psr\Http\Message\RequestInterface;
use whm\Html\Uri;

class HeadlessChromeClient implements HttpClient
{
    const CLIENT_TYPE = "headless_chrome";

    public function sendRequest(RequestInterface $request)
    {
        $file = sys_get_temp_dir() . md5(microtime()) . '.json';

        $uri = $request->getUri();
        /** @var Uri $uri */
        $cookieString = $uri->getCookieString();

        exec('node ' . __DIR__ . '/Puppeteer/puppeteer.js ' . (string)$request->getUri() . ' 40000 ' . $cookieString . '> ' . $file, $output, $return);

        $responseJson = file_get_contents($file);
        unlink($file);

        if (strpos($responseJson, 'TIMEOUT') === 0) {
            throw new TimeOutException('Timeout. Unable to GET ' . (string)$request->getUri() . '.');
        }

        $plainResponse = json_decode($responseJson, true);

        if (array_key_exists('type', $plainResponse) and $plainResponse['type'] == 'error') {
            throw new \Exception('Unable to GET ' . (string)$request->getUri() . '. Error message: ' . $plainResponse['message']);
        }

        $requests = $plainResponse['requests'];

        $masterRequest = array_pop($requests);

        $resources = array();

        foreach ($requests as $key => $resource) {
            $resources[] = ['name' => $key];
        }

        if (!array_key_exists('http_status', $masterRequest)) {
            throw new \RuntimeException('Unable to GET ' . (string)$request->getUri() . '. HTTP Status not set');
        }

        $response = new HeadlessChromeResponse($masterRequest['http_status'], $plainResponse['bodyHTML'], $request, $resources, $masterRequest['response_headers'], $request->getUri());
        $response->setJavaScriptErrors($plainResponse['js_errors']);
        return $response;
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
