<?php

namespace phm\HttpWebdriverClient\Http;

use Psr\Http\Message\ResponseInterface;

interface ResourcesAwareResponse extends ResponseInterface
{
    public function getResources();
}