<?php

namespace phm\HttpWebdriverClient\Http\Response;

use Psr\Http\Message\ResponseInterface;

interface ScreenshotAwareResponse extends ResponseInterface
{
    public function setScreenshotFromFile($screenshotPath);

    public function getScreenshot();

    public function getScreenshotString();

    public function hasScreenshot();
}