<?php

namespace phm\HttpWebdriverClient\Http\Response;

use Psr\Http\Message\ResponseInterface;

interface CookieAwareResponse extends ResponseInterface
{
    public function getCookies($domain = null);

    public function getCookieCount();
}