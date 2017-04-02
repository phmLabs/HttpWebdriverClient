<?php

namespace phm\HttpWebdriverClient\Http;

use Psr\Http\Message\RequestInterface;

interface HttpClient
{
    /**
     * @param RequestInterface $request
     * @return Response
     * @throws \Exception
     */
    public function sendRequest(RequestInterface $request);
}
