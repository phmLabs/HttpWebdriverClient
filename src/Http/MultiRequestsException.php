<?php

namespace phm\HttpWebdriverClient\Http;

class MultiRequestsException extends \Exception
{
    private $exceptions = [];

    public function __construct($exceptions = [])
    {
        $this->exceptions = $exceptions;
    }

    /**
     * @return \Exception[]
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }
}