<?php

namespace phm\HttpWebdriverClient\Http\Response;

use Psr\Http\Message\ResponseInterface;

interface ContentLengthAwareResponse extends ResponseInterface
{
    /**
     * @return int
     */
    public function getContentLength();
}