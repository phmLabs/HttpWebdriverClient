<?php

namespace phm\HttpWebdriverClient\Http\Client\HeadlessChrome;

use phm\HttpWebdriverClient\Http\Client\Chrome\ChromeResponse;
use phm\HttpWebdriverClient\Http\Response\CookieAwareResponse;
use phm\HttpWebdriverClient\Http\Response\DomAwareResponse;
use phm\HttpWebdriverClient\Http\Response\RequestAwareResponse;
use phm\HttpWebdriverClient\Http\Response\ScreenshotAwareResponse;
use phm\HttpWebdriverClient\Http\Response\TimeoutAwareResponse;

class HeadlessChromeResponse extends ChromeResponse implements \JsonSerializable, TimeoutAwareResponse, ScreenshotAwareResponse, CookieAwareResponse, RequestAwareResponse, DomAwareResponse
{
    private $isTimeout = false;
    private $screenshot;

    private $cookies = array();

    private $htmlBody;

    public function setIsTimeout()
    {
        $this->isTimeout = true;
    }

    public function isTimeout()
    {
        return $this->isTimeout;
    }

    public function setScreenshotFromFile($screenshotPath)
    {
        $this->screenshot = imagecreatefrompng($screenshotPath);
    }

    public function getScreenshot()
    {
        return $this->screenshot;
    }

    public function hasScreenshot()
    {
        return !is_null($this->screenshot);
    }

    public function setCookies($cookieArray)
    {
        foreach ($cookieArray as $domain => $cookies) {
            foreach ($cookies as $cookie) {
                $this->cookies[$domain][$cookie['name']] = $cookie;
            }
        }
    }

    public function getCookies($domain = null)
    {
        if ($domain) {
            return $this->cookies[$domain];
        } else {
            return $this->cookies;
        }
    }

    public function getCookieCount()
    {
        $count = 0;
        foreach ($this->cookies as $cookies) {
            $count += count($cookies);
        }
        return $count;
    }

    public function getHtmlBody()
    {
        return $this->htmlBody;
    }

    public function getDomBody()
    {
        return $this->getBody();
    }

    public function setHtmlBody($htmlBody)
    {
        $this->htmlBody = $htmlBody;
    }

    public function jsonSerialize()
    {
        return [
            'statusCode' => $this->getStatusCode(),
            // 'domBody' => $this->getDomBody(),
            // 'htmlBody' => $this->getHtmlBody(),
            'headers' => $this->getHeaders(),
            'cookies' => $this->getCookies(),
            ''
        ];
    }
}
