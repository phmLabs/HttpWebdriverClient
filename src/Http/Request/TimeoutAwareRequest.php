<?php

namespace phm\HttpWebdriverClient\Http\Request;

use Psr\Http\Message\RequestInterface;

interface TimeoutAwareRequest extends RequestInterface
{
    /**
     * The the timeout time in milliseconds
     *
     * @return integer
     */
    public function getTimeout();

    /**
     * Get the rule for timeout handling
     *
     * In case of Geppetto it equals the waitUntil method
     * @see https://pptr.dev/#?product=Puppeteer&version=v2.1.0&show=api-pagegotourl-options
     *
     * @return string
     */
    public function getTimeoutRule();
}
