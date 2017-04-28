<?php

namespace phm\HttpWebdriverClient\Http;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use whm\Html\Uri;

class ChromeClient implements HttpClient
{
    const COOKIE_HEADER = '__leankoala_headers';

    private $webdriverHost;
    private $webdriverPort;
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
    }

    private function getWebdriverHost()
    {
        return $this->webdriverHost . ':' . $this->webdriverPort . '/wd/hub';
    }

    /**
     * @param bool $withCookieHandling
     * @return RemoteWebDriver
     */
    private function getDriver($withCookieHandling = true)
    {
        if (!$this->keepAlive || !$this->driver instanceof RemoteWebDriver) {
            $options = new ChromeOptions();

            $options->addArguments(array('--window-size=2024,2000'));

            if ($withCookieHandling) {
                $options->addExtensions(array(
                    __DIR__ . '/../../extension/cookie_extension.crx',
                    __DIR__ . '/../../extension/requests.crx'
                ));
            } else {
                $options->addExtensions(array(
                    __DIR__ . '/../../extension/requests.crx'
                ));
            }

            $caps = DesiredCapabilities::chrome();

            $caps->setCapability(ChromeOptions::CAPABILITY, $options);

            $driver = RemoteWebDriver::create($this->getWebdriverHost(), $caps);
        } else {
            $driver = $this->driver;
        }
        return $driver;
    }

    public function __destruct()
    {
        if ($this->driver) {
            $this->driver->quit();
        }
    }

    /**
     * @param RequestInterface $request
     * @return ResourcesAwareResponse
     * @throws \Exception
     */
    public function sendRequest(RequestInterface $request)
    {
        $uri = $request->getUri();

        $driver = $this->getDriver();

        if ($uri instanceof Uri && $uri->hasCookies()) {
            $finalUrl = (string)$uri . '#cookie=' . $uri->getCookieString();
        } else {
            $finalUrl = (string)$uri;
        }

        $driver->get($finalUrl);
        $driver->executeScript('performance.setResourceTimingBufferSize(500);');
        sleep($this->sleepTime);

        $html = $driver->executeScript('return document.documentElement.outerHTML');
        $resources = $driver->executeScript('return performance.getEntriesByType(\'resource\')');

        $headers = $this->getHeaders($driver);

        if (isset($driver) && !$this->keepAlive) {
            $driver->quit();
        }

        $response = new ChromeResponse(200, $html, $request, $resources, $headers);

        return $response;
    }

    private function getHeaders(RemoteWebDriver $driver)
    {
        $headerInfosBase = $driver->manage()->getCookieNamed(self::COOKIE_HEADER);
        $headerInfosJson = base64_decode($headerInfosBase['value']);
        $headerInfos = json_decode($headerInfosJson);

        $headers = [];

        foreach ($headerInfos as $headerInfo) {
            $headers[$headerInfo->name] = $headerInfo->value;
        }

        return $headers;
    }
}