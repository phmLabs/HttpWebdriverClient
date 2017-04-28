<?php

namespace phm\HttpWebdriverClient\Http;

use Psr\Http\Message\ResponseInterface;

interface DurationAwareResponse extends ResponseInterface
{
    public function getDuration();
}