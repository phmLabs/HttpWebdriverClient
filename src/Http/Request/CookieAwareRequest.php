<?php

namespace phm\HttpWebdriverClient\Http\Request;

use Psr\Http\Message\RequestInterface;

interface CookieAwareRequest extends RequestInterface
{
    public function withCookies(array $cookies);
}