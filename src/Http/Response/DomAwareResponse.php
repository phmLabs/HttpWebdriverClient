<?php

namespace phm\HttpWebdriverClient\Http\Response;

use Psr\Http\Message\ResponseInterface;

interface DomAwareResponse extends ResponseInterface
{
    public function getHtmlBody();

    public function getDomBody();
}