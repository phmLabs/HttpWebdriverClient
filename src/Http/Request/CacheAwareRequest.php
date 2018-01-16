<?php

namespace phm\HttpWebdriverClient\Http\Request;

use Psr\Http\Message\RequestInterface;

interface CacheAwareRequest extends RequestInterface
{
    /**
     * @param string $hash
     */
    public function getHash();

    /**
     * @param boolean $isAllowed
     */
    public function setIsCacheAllowed($isAllowed);

    /**
     * @return boolean
     */
    public function isCacheAllowed();
}