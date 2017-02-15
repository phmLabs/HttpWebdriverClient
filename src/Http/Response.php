<?php

namespace phm\HttpWebdriverClient\Http;

use Psr\Http\Message\RequestInterface;

class Response
{
    private $body;
    private $requests;

    /**
     * @var RequestInterface
     */
    private $request;

    public function getRequest()
    {
        return $this->request;
    }

    public function __construct($body, $requests, RequestInterface $request)
    {
        $this->requests = $requests;
        $this->request = $request;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getRequestCount($urlPattern)
    {
        $foundCount = 0;

        foreach ($this->requests as $request) {
            $url = $request['name'];
            if (preg_match('`' . $urlPattern . '`', $url)) {
                ++$foundCount;
            }
        }

        return $foundCount;
    }

    public function getRequests()
    {
        return $this->requests;
    }
}