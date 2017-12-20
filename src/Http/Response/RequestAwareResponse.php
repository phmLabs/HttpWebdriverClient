<?php

namespace phm\HttpWebdriverClient\Http\Response;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface RequestAwareResponse extends ResponseInterface
{
    /**
     * @return RequestInterface
     */
    public function getRequest();
}