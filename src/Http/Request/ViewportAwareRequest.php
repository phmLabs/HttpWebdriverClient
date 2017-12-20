<?php

namespace phm\HttpWebdriverClient\Http\Request;

use Psr\Http\Message\RequestInterface;

interface ViewportAwareRequest extends RequestInterface
{
    /**
     * @param Viewport $viewport
     */
    public function setViewport(Viewport $viewport);

    /**
     * @return Viewport
     */
    public function getViewport();
}