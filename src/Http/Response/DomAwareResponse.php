<?php

namespace phm\HttpWebdriverClient\Http\Response;

use Psr\Http\Message\ResponseInterface;

interface DomAwareResponse extends ResponseInterface
{
    /**
     * @return string
     */
    public function getHtmlBody();

    /**
     * @return string
     */
    public function getDomBody();
}