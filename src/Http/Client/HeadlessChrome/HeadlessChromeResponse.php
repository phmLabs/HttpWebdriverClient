<?php

namespace phm\HttpWebdriverClient\Http\Client\HeadlessChrome;

use phm\HttpWebdriverClient\Http\Client\Chrome\ChromeResponse;
use phm\HttpWebdriverClient\Http\Response\ScreenshotAwareResponse;
use phm\HttpWebdriverClient\Http\Response\TimeoutAwareResponse;

class HeadlessChromeResponse extends ChromeResponse implements TimeoutAwareResponse, ScreenshotAwareResponse
{
    private $isTimeout = false;
    private $screenshot;

    public function setIsTimeout()
    {
        $this->isTimeout = true;
    }

    public function isTimeout()
    {
        return $this->isTimeout;
    }

    public function setScreenshot($screenshotPath)
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
}
