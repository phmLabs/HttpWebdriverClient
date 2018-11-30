<?php

namespace phm\HttpWebdriverClient\Http\Response;

use phm\HttpWebdriverClient\Http\Response\UnfinishedRequest\Request;

interface UnfinishedRequestAwareResponse
{
    /**
     * @return Request[]
     */
    public function getUnfinishedRequests();
}