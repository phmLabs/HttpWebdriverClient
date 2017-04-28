<?php

namespace phm\HttpWebdriverClient\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;


class Response
{
    /**
     * The html body
     *
     * @var String
     */
    private $body;

    private $requests;

    private $headers = [];

    /**
     * @var RequestInterface
     */
    private $request;

    public function getRequest()
    {
        return $this->request;
    }

    public function __construct($body, array $requests, RequestInterface $request, $headers = [])
    {
        $this->requests = $requests;
        $this->request = $request;
        $this->headers = $headers;
        $this->body = $body;
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

    public function getHeaders()
    {
        return $this->headers;
    }
}
