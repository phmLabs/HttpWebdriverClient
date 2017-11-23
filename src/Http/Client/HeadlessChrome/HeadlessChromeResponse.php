<?php

namespace phm\HttpWebdriverClient\Http\Client\HeadlessChrome;

use phm\HttpWebdriverClient\Http\Client\Chrome\ChromeResponse;
use phm\HttpWebdriverClient\Http\Response\TimeoutAwareResponse;

class HeadlessChromeResponse extends ChromeResponse implements TimeoutAwareResponse
{
    private $isTimeout = false;

    public function setIsTimeout()
    {
        $this->isTimeout = true;
    }

    public function isTimeout()
    {
        return $this->isTimeout;
    }
}
