<?php

namespace phm\HttpWebdriverClient\Http\Client;

use phm\HttpWebdriverClient\Http\MultiRequestsException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpClient
{
    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function sendRequest(RequestInterface $request);

    /**
     * @param RequestInterface[] $requests
     * @return ResponseInterface[]
     * @throws MultiRequestsException
     */
    public function sendRequests(array $requests);


    /**
     * This function returns the type of the client.
     *
     * @return string
     */
    public function getClientType();
}
