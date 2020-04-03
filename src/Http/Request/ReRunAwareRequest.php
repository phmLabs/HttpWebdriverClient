<?php

namespace phm\HttpWebdriverClient\Http\Request;

use Psr\Http\Message\RequestInterface;

interface ReRunAwareRequest extends RequestInterface
{
    /**
     * @return array
     */
    public function getRerunRules();
}
