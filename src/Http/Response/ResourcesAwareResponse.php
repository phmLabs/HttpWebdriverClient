<?php

namespace phm\HttpWebdriverClient\Http\Response;

use Psr\Http\Message\ResponseInterface;

interface ResourcesAwareResponse extends ResponseInterface
{
    /**
     * @return array
     */
    public function getResources();

    public function getResourceCount($pattern);
}