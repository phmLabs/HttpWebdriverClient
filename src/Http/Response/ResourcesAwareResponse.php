<?php

namespace phm\HttpWebdriverClient\Http\Response;

use Psr\Http\Message\ResponseInterface;

interface ResourcesAwareResponse extends ResponseInterface
{
    public function getResources();
}