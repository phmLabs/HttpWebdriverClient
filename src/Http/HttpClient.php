<?php

namespace phm\HttpWebdriverClient\Http;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Psr\Http\Message\RequestInterface;
use whm\Html\Uri;

class HttpClient
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

        $options->addArguments(array('--window-size=2024,2000'));

        $uri = $request->getUri();

        $finalUrl = (string)$uri;

        if ($uri instanceof Uri) {
            if ($uri->hasCookies()) {
                $options->addExtensions(array(
                    __DIR__ . '/../../extension/cookie_extension.crx'
                ));
                $finalUrl = $finalUrl . '#cookie=' . $uri->getCookieString();
            }
        }

        $caps = DesiredCapabilities::chrome();

        $caps->setCapability(ChromeOptions::CAPABILITY, $options);


        $driver = RemoteWebDriver::create($this->getWebdriverHost(), $caps);

        $driver->get($finalUrl);
        $driver->executeScript('performance.setResourceTimingBufferSize(400);');
        sleep(1);

        $html = $driver->executeScript('return document.documentElement.outerHTML');
        $resources = $driver->executeScript('return performance.getEntriesByType(\'resource\')');


        $driver->takeScreenshot('/tmp/scree.png');

        if (isset($driver)) {
            $driver->quit();
        }

        return new Response($html, $resources, $request);
    }
}
