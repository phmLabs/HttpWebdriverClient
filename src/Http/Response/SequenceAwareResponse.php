<?php

namespace phm\HttpWebdriverClient\Http\Response;

use Psr\Http\Message\ResponseInterface;

interface SequenceAwareResponse extends ResponseInterface
{
    public function setSequenceResult($result);

    /**
     * @return mixed[]
     */
    public function getSequenceResult();
}