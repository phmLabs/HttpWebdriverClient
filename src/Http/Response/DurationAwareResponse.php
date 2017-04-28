<?php

namespace phm\HttpWebdriverClient\Http\Response;

use Psr\Http\Message\ResponseInterface;

interface DurationAwareResponse extends ResponseInterface
{
    public function getDuration();
}