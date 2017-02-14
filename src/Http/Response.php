<?php

namespace phm\HttpWebdriverClient\Http;

class Response
{
    private $body;
    private $requests;

    public function __construct($body, $requests)
    {
        $this->requests = $requests;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function hasRequest($urlPattern)
    {
        foreach ($this->requests as $request) {
            $url = $request['name'];
            if (preg_match('`' . $urlPattern . '`', $url)) {
                return true;
            }
        }

        return false;
    }

    public function getRequests()
    {
        return $this->requests;
    }
}