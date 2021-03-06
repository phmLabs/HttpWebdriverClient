<?php

namespace phm\HttpWebdriverClient\Http\Response;

use Psr\Http\Message\ResponseInterface;

interface TimeoutAwareResponse extends ResponseInterface
{
    /**
     * @return boolean
     */
    public function isTimeout();

    /**
     * Returns the timeout time in milliseconds
     *
     * @return integer
     */
    public function getTimeoutTime();
}