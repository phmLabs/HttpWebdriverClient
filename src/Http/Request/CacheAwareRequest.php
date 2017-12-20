<?php

namespace phm\HttpWebdriverClient\Http\Request;

use Psr\Http\Message\RequestInterface;

interface CacheAwareRequest extends RequestInterface
{
    /**
     * @param string $hash
     */
    public function getHash();
}