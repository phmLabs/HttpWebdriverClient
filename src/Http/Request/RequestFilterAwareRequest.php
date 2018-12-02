<?php

namespace phm\HttpWebdriverClient\Http\Request;

use Psr\Http\Message\RequestInterface;

interface RequestFilterAwareRequest extends RequestInterface
{
    /**
     * A list of regular expressions that should be filtered
     * @return string[]
     */
    public function getFilteredRequests();
}