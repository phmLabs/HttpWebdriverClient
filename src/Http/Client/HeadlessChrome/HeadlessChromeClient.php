<?php

namespace phm\HttpWebdriverClient\Http\Client\HeadlessChrome;

use phm\HttpWebdriverClient\Http\Client\HttpClient;
use phm\HttpWebdriverClient\Http\Client\TimeOutException;
use Psr\Http\Message\RequestInterface;
use whm\Html\Uri;

class HeadlessChromeClient implements HttpClient
{
    const CLIENT_TYPE = "headless_chrome";

    private $chromeTimeout;

    public function __construct($chromeTimeOut = 31000)
    {
        $this->chromeTimeout = $chromeTimeOut;
    }

    public function sendRequest(RequestInterface $request)
    {
        $plainResponse = $this->sendHeadlessChromeRequest($request);
        $requests = $plainResponse['requests'];

        $masterRequest = array_shift($requests);

        $resources = array();

        foreach ($requests as $key => $resource) {
            $resourceElement = ['name' => $key];

            if (array_key_exists('http_status', $resource)) {
                $resourceElement['http_status'] = $resource['http_status'];
            } else {
                $resourceElement['http_status'] = -1;
            }
            $resources[] = $resourceElement;
        }

        $content = $this->repairContent($plainResponse['bodyHTML']);

        $response = new HeadlessChromeResponse($masterRequest['http_status'], $content, $request, $resources, $masterRequest['response_headers'], $request->getUri());
        $response->setJavaScriptErrors($plainResponse['js_errors']);

        if ($plainResponse['screenshot']) {
            $response->setScreenshot($plainResponse['screenshot']);
        }

        if ($plainResponse['status'] == 'timeout') {
            $response->setIsTimeout();
        }

        if (array_key_exists('navigation', $plainResponse['timing']) && array_key_exists('requestStart', $plainResponse['timing']['navigation'])) {
            $requestStart = $plainResponse['timing']['navigation']['requestStart'];
            $responseStart = $plainResponse['timing']['navigation']['responseStart'];
            $duration = $responseStart - $requestStart;
        } else {
            $startTime = $masterRequest["time_start"];
            $stopTime = $masterRequest["time_finished"];
            $duration = $stopTime - $startTime;
        }
        $response->setDuration($duration);

        $response->setCookies($plainResponse['cookies']);

        return $response;
    }

    private function repairContent($content)
    {
        $content = str_replace('<iframe style="position: absolute; top: -10000px; left: -1000px;"></iframe>', '', $content);
        return $content;
    }

    private function sendHeadlessChromeRequest(RequestInterface $request, $retries = 2)
    {
        $exception = null;

        for ($i = 0; $i < $retries; $i++) {
            try {
                $response = $this->processHeadlessChromeRequest($request);
                return $response;
            } catch (\Exception $e) {
                $exception = $e;
            }
        }

        throw  $exception;
    }

    private function processHeadlessChromeRequest(RequestInterface $request)
    {
        $file = sys_get_temp_dir() . md5(microtime()) . '.json';

        $uri = $request->getUri();
        /** @var Uri $uri */
        $cookieString = $uri->getCookieString();

        $command = 'node ' . __DIR__ . '/Puppeteer/puppeteer.js "' . (string)$request->getUri() . '" ' . $this->chromeTimeout . ' "' . $cookieString . '" > ' . $file;
        exec($command, $output, $return);

        $responseJson = trim(file_get_contents($file));
        unlink($file);

        if (strpos($responseJson, 'TIMEOUT') === 0) {
            throw new TimeOutException('Timeout. Unable to GET ' . (string)$request->getUri() . '.');
        }

        $plainResponse = json_decode($responseJson, true);
        $requests = $plainResponse['requests'];

        if (!$plainResponse) {
            throw new \RuntimeException('Error occured: ' . $responseJson);
        }

        if (array_key_exists('type', $plainResponse) and $plainResponse['type'] == 'error') {
            throw new \Exception('Unable to GET ' . (string)$request->getUri() . '. Error message: ' . $plainResponse['message']);
        }

        $masterRequest = array_shift($requests);
        if (!array_key_exists('http_status', $masterRequest)) {
            throw new \RuntimeException('Unable to GET ' . (string)$request->getUri() . '. HTTP Status Code not set.');
        }

        return $plainResponse;
    }

    public function sendRequests(array $requests)
    {
        $responses = array();

        foreach ($requests as $request) {
            try {
                $responses[] = $this->sendRequest($request);
            } catch (\Exception $e) {
                echo "Error sending request " . $request->getUri() . '. Message: ' . $e->getMessage() . '.';
            }
        }

        return $responses;
    }

    public function getClientType()
    {
        return self::CLIENT_TYPE;
    }

    /**
     * This function closes the browser connection.
     *
     * It is not needed for headless chrome but the interface forces it.
     */
    public function close()
    {
    }

    public function setOption($key, $value)
    {
    }
}
