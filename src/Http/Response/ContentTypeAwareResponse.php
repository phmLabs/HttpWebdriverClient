<?php

namespace phm\HttpWebdriverClient\Http\Response;

use Psr\Http\Message\ResponseInterface;

interface ContentTypeAwareResponse extends ResponseInterface
{
    public function getContentType();
}