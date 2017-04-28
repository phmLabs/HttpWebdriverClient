<?php

namespace phm\HttpWebdriverClient\Http\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

interface UriAwareResponse extends ResponseInterface
{
    /**
     * @return UriInterface
     */
    public function getUri();
}