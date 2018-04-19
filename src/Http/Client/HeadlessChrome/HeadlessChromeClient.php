<?php

namespace phm\HttpWebdriverClient\Http\Client\HeadlessChrome;

use Leankoala\Devices\DeviceFactory;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use phm\HttpWebdriverClient\Http\Client\TimeOutException;
use phm\HttpWebdriverClient\Http\Request\UserAgentAwareRequest;
use phm\HttpWebdriverClient\Http\Request\ViewportAwareRequest;
use Psr\Http\Message\RequestInterface;

class HeadlessChromeClient implements HttpClient
{
    const CLIENT_TYPE = "headless_chrome";

    const DEFAULT_DEVICE_IDENTIFIER = 'MacBookPro152017';

    private $chromeTimeout;

    private $defaultdDevice;

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

            if (array_key_exists('type', $resource)) {
                $resourceElement['type'] = $resource['type'];
            } else {
                $resourceElement['type'] = 'unknown';
            }

            if (array_key_exists('http_status', $resource)) {
                $resourceElement['http_status'] = $resource['http_status'];
            } else {
                $resourceElement['http_status'] = -1;
            }
            $resources[] = $resourceElement;
        }

        $content = $this->repairContent($plainResponse['bodyDOM']);
        if (!$content) {
            $content = $plainResponse['bodyHTML'];
        }

        $response = new HeadlessChromeResponse($masterRequest['http_status'], $content, $request, $resources, $masterRequest['response_headers'], $request->getUri());
        $response->setJavaScriptErrors($plainResponse['js_errors']);

        $response->setHtmlBody($plainResponse['bodyHTML']);

        if ($plainResponse['screenshot']) {
            $response->setScreenshotFromFile($plainResponse['screenshot']);
            unlink($plainResponse['screenshot']);
        }

        if ($plainResponse['status'] == 'timeout') {
            $response->setIsTimeout();
        }

        if (array_key_exists('navigation', $plainResponse['timing']) && array_key_exists('requestStart', $plainResponse['timing']['navigation'])) {
            $requestStart = $plainResponse['timing']['navigation']['requestStart'];
            $responseStart = $plainResponse['timing']['navigation']['responseStart'];
            $duration = $responseStart - $requestStart;
            $timeToLoad = $plainResponse['timing']['navigation']['loadEventEnd'] - $plainResponse['timing']['navigation']['requestStart'];
            $response->setTimingLoad($timeToLoad);
        } else {
            $startTime = $masterRequest["time_start"];
            $stopTime = $masterRequest["time_finished"];
            $duration = $stopTime - $startTime;
        }

        $response->setDuration($duration);
        $response->setTimingTtfb($duration);

        $response->setCookies($plainResponse['cookies']);

        return $response;
    }

    private function repairContent($content)
    {
        $brokenPatterns = [
            '<iframe(.*?)</iframe>',
            '<div(.*?)</div>'
        ];

        preg_match('@<head(.*)</head>@s', $content, $matches);

        if (count($matches) > 0) {
            $head = $matches[0];

            foreach ($brokenPatterns as $brokenPattern) {
                preg_match_all('@' . $brokenPattern . '@s', $head, $iframeMatches);

                if (count($iframeMatches) > 0) {
                    $foundIframes = (array)$iframeMatches[0];
                    foreach ($foundIframes as $foundIframe) {
                        $content = str_replace($foundIframe, '', $content);
                    }
                }
            }
        }

        return $content;
    }

    /**
     * @param RequestInterface $request
     * @param int $retries
     * @return mixed
     * @throws \Exception
     */
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

        throw $exception;
    }

    private function getDefaultDevice()
    {
        if (!$this->defaultdDevice) {
            $factory = new DeviceFactory();
            $this->defaultdDevice = $factory->create(self::DEFAULT_DEVICE_IDENTIFIER);
        }
        return $this->defaultdDevice;
    }

    private function getCookieString(RequestInterface $request)
    {
        $cookieHeader = $request->getHeader('cookie');

        if (count($cookieHeader) > 0) {
            return $cookieHeader[0];
        } else {
            return '';
        }
    }

    /**
     * @param RequestInterface $request
     * @return mixed
     * @throws \Exception
     */
    private function processHeadlessChromeRequest(RequestInterface $request)
    {
        $file = sys_get_temp_dir() . md5(microtime()) . '.json';

        $cookieString = $this->getCookieString($request);

        if ($request instanceof ViewportAwareRequest) {
            $viewport = $request->getViewport();
            $viewportJson = json_encode($viewport);
        } else {
            $viewportJson = json_encode($this->getDefaultDevice()->getViewport());
        }

        if ($request instanceof UserAgentAwareRequest) {
            $userAgent = $request->getUserAgent();
        } else {
            $userAgent = $this->getDefaultDevice()->getUserAgent();
        }

        $command = 'node ' . __DIR__ . '/Puppeteer/puppeteer.js "' . (string)$request->getUri() . '" ' . $this->chromeTimeout . ' "' . $cookieString . '" "' . $userAgent . '" \'' . $viewportJson . '\' > ' . $file;
        // var_dump($command);
        exec($command, $output, $return);

        $responseJson = trim(file_get_contents($file));
        unlink($file);

        if (strpos($responseJson, 'TIMEOUT') === 0) {
            throw new TimeOutException('Timeout. Unable to GET ' . (string)$request->getUri() . '.');
        }

        $plainResponse = json_decode($responseJson, true);

        if (!$plainResponse) {
            throw new \RuntimeException('Error occured: ' . $responseJson);
        }

        $requests = $plainResponse['requests'];

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
                echo "Error sending request " . $request->getUri() . '. Message: ' . /*$e->getMessage() . */
                    '.';
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
