<?php

namespace phm\HttpWebdriverClient\Http\Response;

use Psr\Http\Message\ResponseInterface;

interface ScreenshotAwareResponse extends ResponseInterface
{
    public function getScreenshot();

    public function hasScreenshot();
}