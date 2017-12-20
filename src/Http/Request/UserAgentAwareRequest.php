<?php

namespace phm\HttpWebdriverClient\Http\Request;

use Psr\Http\Message\RequestInterface;

interface UserAgentAwareRequest extends RequestInterface
{
    /**
     * @param string $userAgent
     */
    public function setUserAgent($userAgent);

    /**
     * @return string
     */
    public function getUserAgent();
}