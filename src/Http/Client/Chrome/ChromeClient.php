<?php

namespace phm\HttpWebdriverClient\Http\Client\Chrome;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use phm\HttpWebdriverClient\Http\MultiRequestsException;
use phm\HttpWebdriverClient\Http\Response\DetailedResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use whm\Html\Uri;

declare(ticks = 1);

class ChromeClient implements HttpClient
{
    const COOKIE_HEADER = '__leankoala_headers';

    private $webdriverHost;
    private $webdriverPort;

    private $screenshoots = [];

    private static $contentType = [
        'xml' => ['text/xml', 'application/xml'],
        'json' => ['application/json', 'text/json']
    ];

    /**
     * The time chrome waits for elements to be rendered.
     * @var int
     */
    private $sleepTime;

    /**
     * @var RemoteWebDriver
     */
    private $driver;
    private $keepAlive;

    /**
     * HttpAdapter constructor.
     * @param $webdriverHost
     * @param $webdriverPort
     */
    public function __construct($webdriverHost = 'localhost', $webdriverPort = '4444', $sleepTime = 1, $keepAlive = false)
    {
        $this->webdriverHost = $webdriverHost;
        $this->webdriverPort = $webdriverPort;
        $this->sleepTime = $sleepTime;
        $this->keepAlive = $keepAlive;

        $this->registerSignalHandling();
    }

    /**
     * If the php process gets killed by CTLR-C or timeout --signal=SIGINT
     */
    private function registerSignalHandling()
    {
        pcntl_signal(SIGINT, function ($signal) {
            echo 'Process killed by signal ' . $signal . ": closing webdriver connection.\n";
            $this->close();
            die(1);
        });
    }

    private function getWebdriverHost()
    {
        return $this->webdriverHost . ':' . $this->webdriverPort . '/wd/hub';
    }

    /**
     * @param boolean $withCookieHandling
     * @return RemoteWebDriver
     */
    private function getDriver($withCookieHandling = true)
    {
        if (!$this->keepAlive || !$this->driver instanceof RemoteWebDriver) {
            $options = new ChromeOptions();

            $options->addArguments(array('--window-size=2024,2000'));

            // $options->addArguments(Proxy = null)

            if ($withCookieHandling) {
                $options->addExtensions(array(
                    __DIR__ . '/extension/cookie_extension.crx',
                    __DIR__ . '/extension/requests.crx'
                ));
            } else {
                $options->addExtensions(array(
                    __DIR__ . '/extension/requests.crx'
                ));
            }

            $caps = DesiredCapabilities::chrome();

            $caps->setCapability(ChromeOptions::CAPABILITY, $options);
            // $caps->setCapability(WebDriverCapabilityType::PROXY, null);

            $driver = RemoteWebDriver::create($this->getWebdriverHost(), $caps);
        } else {
            $driver = $this->driver;
        }
        return $driver;
    }

    private function getResponseInfo(RemoteWebDriver $driver)
    {
        $headerInfosBase = $driver->manage()->getCookieNamed(self::COOKIE_HEADER);
        $headerInfosJson = base64_decode($headerInfosBase['value']);
        $responseInfos = json_decode($headerInfosJson);

        $responseHeaders = $responseInfos->responseHeaders;

        $headers = [];

        foreach ($responseHeaders as $headerInfo) {
            $headers[$headerInfo->name] = $headerInfo->value;
        }

        preg_match('@HTTP/(.*?) @', $responseInfos->statusLine, $matches);
        $protocol = $matches[1];

        return [
            'headers' => $headers,
            'statusCode' => $responseInfos->statusCode,
            'protocol' => $protocol
        ];
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close()
    {
        if ($this->driver) {
            $this->driver->quit();
        }
    }

    /**
     * @param RequestInterface $request
     * @return DetailedResponse
     * @throws \Exception
     */
    public function sendRequest(RequestInterface $request, $createScreenshot = false)
    {
        if ($request->getMethod() != 'GET') {
            throw new \RuntimeException('The given method "' . $request->getMethod() . '" is supported.');
        }

        $uri = $request->getUri();
        $driver = $this->getPreparedDriver($uri);

        $html = $driver->executeScript('return document.documentElement.outerHTML;');
        $resources = $driver->executeScript('return performance.getEntriesByType(\'resource\');');

        $navigation = $driver->executeScript('return performance.timing;');
        $duration = $navigation['responseStart'] - $navigation['requestStart'];
        
        $effectiveUri = new \GuzzleHttp\Psr7\Uri($driver->getCurrentURL());

        $responseInfo = $this->getResponseInfo($driver);

        if (isset($driver) && !$this->keepAlive) {
            $driver->quit();
        }

        $response = new ChromeResponse(
            $responseInfo['statusCode'],
            $html,
            $request,
            $resources,
            $responseInfo['headers'],
            $effectiveUri
        );

        $response->setProtocolVersion($responseInfo['protocol']);
        $response->setDuration($duration);

        if ($this->isContentType($response, 'xml')) {
            $response = $this->extractXmlResponse($response);
        } elseif ($this->isContentType($response, 'json')) {
            $response = $this->extractJsonResponse($response);
        }

        return $response;
    }

    private function getPreparedDriver(UriInterface $uri)
    {
        $driver = $this->getDriver();

        if ($uri instanceof Uri && $uri->hasCookies()) {
            $finalUrl = (string)$uri . '#cookie=' . $uri->getCookieString();
        } else {
            $finalUrl = (string)$uri;
        }

        $driver->get($finalUrl);

        $driver->executeScript('performance.setResourceTimingBufferSize(500);');
        sleep($this->sleepTime);

        return $driver;
    }

    private function isContentType(ChromeResponse $response, $type)
    {
        return (in_array($response->getContentType(), self::$contentType[$type]));
    }

    private function extractXmlResponse(ResponseInterface $response)
    {
        $html = (string)$response->getBody();
        if (strpos($html, 'webkit-xml-viewer-source-xml') !== false) {
            preg_match('#<div id="webkit-xml-viewer-source-xml">(.*?)<\/div>#', $html, $matches);
            if (count($matches) > 0) {
                $response = $response->withBody(\GuzzleHttp\Psr7\stream_for($matches[1]));
            }
        }

        return $response;
    }

    private function extractJsonResponse(ResponseInterface $response)
    {
        $html = (string)$response->getBody();
        if (strpos($html, 'word-wrap: break-word; white-space: pre-wrap;') !== false) {
            preg_match('#<pre style="word-wrap: break-word; white-space: pre-wrap;">(.*?)<\/pre>#', $html, $matches);
            if (count($matches) > 0) {
                $response = $response->withBody(\GuzzleHttp\Psr7\stream_for($matches[1]));
            }
        }

        return $response;
    }

    /**
     * @param RequestInterface[] $requests
     * @return ChromeResponse[]
     * @throws MultiRequestsException
     */
    public function sendRequests(array $requests)
    {
        $responses = [];
        $exceptions = [];

        foreach ($requests as $request) {
            try {
                $responses[] = $this->sendRequest($request);
            } catch (\Exception $e) {
                $exceptions[] = $e;
            }
        }

        // @todo all requests must be added as well
        if (count($exceptions) > 0) {
            throw new MultiRequestsException($exceptions);
        }

        return $responses;
    }

    /**
     * @param bool $keepAlive
     */
    public function setKeepAlive($keepAlive)
    {
        $this->keepAlive = $keepAlive;
    }

    /**
     * @param string $webdriverHost
     */
    public function setWebdriverHost($webdriverHost)
    {
        $this->webdriverHost = $webdriverHost;
    }

    /**
     * @return string
     */
    public function getWebdriverPort()
    {
        return $this->webdriverPort;
    }

    /**
     * @param integer $webdriverPort
     */
    public function setWebdriverPort($webdriverPort)
    {
        $this->webdriverPort = $webdriverPort;
    }

    public function createScreenshot(RequestInterface $request)
    {
        $tmpFileName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . microtime() . '.png';

        $driver = $this->getPreparedDriver($request->getUri());
        $driver->takeScreenshot($tmpFileName);
        $driver->close();

        $image = \imagecreatefrompng($tmpFileName);

        ob_start();
        \imagepng($image);
        $imageString = ob_get_contents();
        ob_end_clean();

        \imagedestroy($image);

        return $imageString;
    }
}
