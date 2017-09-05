<?php

namespace phm\HttpWebdriverClient\Http\Response;

use Psr\Http\Message\ResponseInterface;

interface JavaScriptAwareResponse extends ResponseInterface
{
    public function getJavaScriptErrors();
}