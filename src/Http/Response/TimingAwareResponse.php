<?php

namespace phm\HttpWebdriverClient\Http\Response;

use Psr\Http\Message\ResponseInterface;

interface TimingAwareResponse extends ResponseInterface
{
    /**
     * Return the time to first byte
     *
     * @return integer
     */
    public function getTimeToFirstByte();

    /**
     * Return the time to the load event gets fired in the browser
     *
     * @return integer
     */
    public function getTimeToLoad();
}