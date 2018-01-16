<?php

namespace phm\HttpWebdriverClient\Http\Response;

use Psr\Http\Message\ResponseInterface;

interface CacheAwareResponse extends ResponseInterface
{
    public function setFromCache($fromCache);

    public function isFromCache();
}