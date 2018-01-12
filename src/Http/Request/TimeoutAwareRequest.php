<?php

namespace phm\HttpWebdriverClient\Http\Request;

use Psr\Http\Message\RequestInterface;

interface TimeoutAwareRequest extends RequestInterface
{
    public function getTimeout();
}