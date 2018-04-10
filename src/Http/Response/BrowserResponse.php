<?php

namespace phm\HttpWebdriverClient\Http\Response;

use phm\HttpWebdriverClient\Http\Client\Chrome\ChromeResponse;

class BrowserResponse extends ChromeResponse implements \JsonSerializable, CacheAwareResponse, TimeoutAwareResponse, ScreenshotAwareResponse, CookieAwareResponse, RequestAwareResponse, DomAwareResponse, TimingAwareResponse
{
    private $isTimeout = false;
    private $screenshot;

    private $cookies = array();

    private $htmlBody;

    private $fromCache = false;

    private $timingTtfb = -1;
    private $timingLoad = -1;

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
        if (function_exists('imagecreatefrompng')) {
            $this->screenshot = imagecreatefrompng($screenshotPath);
        }
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

    /**
     * @return bool
     */
    public function isFromCache()
    {
        return $this->fromCache;
    }

    /**
     * @param int $timingTtfb
     */
    public function setTimingTtfb($timingTtfb)
    {
        $this->timingTtfb = $timingTtfb;
    }

    /**
     * @param int $timingLoad
     */
    public function setTimingLoad($timingLoad)
    {
        $this->timingLoad = $timingLoad;
    }

    /**
     * @param bool $fromCache
     */
    public function setFromCache($fromCache)
    {
        $this->fromCache = $fromCache;
    }

    public function getTimeToFirstByte()
    {
        return $this->timingTtfb;
    }

    public function getTimeToLoad()
    {
        return $this->timingLoad;
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