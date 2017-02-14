<?php

namespace phm\HttpWebdriverClient\Http;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Psr\Http\Message\RequestInterface;

class HttpAdapter
{
    private $webdriverHost;
    private $webdriverPort;

    /**
     * HttpAdapter constructor.
     * @param $webdriverHost
     * @param $webdriverPort
     */
    public function __construct($webdriverHost = 'localhost', $webdriverPort = '4444')
    {
        $this->webdriverHost = $webdriverHost;
        $this->webdriverPort = $webdriverPort;
    }

    private function getWebdriverHost()
    {
        return $this->webdriverHost . ':' . $this->webdriverPort . '/wd/hub';
    }

    /**
     * @param RequestInterface $request
     * @return Response
     * @throws \Exception
     */
    public function sendRequest(RequestInterface $request)
    {
        $options = new ChromeOptions();

        #$options->addExtensions(array(
        #    __DIR__ . '/extension/console2var.crx',
        #    __DIR__ . '/cookie_crx/cookie_extension.crx'
        #));

        $caps = DesiredCapabilities::chrome();

        $caps->setCapability(ChromeOptions::CAPABILITY, $options);

        $driver = RemoteWebDriver::create($this->getWebdriverHost(), $caps);
        $driver->get((string)$request->getUri());

        $html = $driver->executeScript('return document.documentElement.outerHTML');
        $resources = $driver->executeScript('return performance.getEntriesByType(\'resource\')');

        return new Response($html, $resources);
    }
}