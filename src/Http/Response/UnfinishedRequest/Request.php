<?php

namespace phm\HttpWebdriverClient\Http\Response\UnfinishedRequest;

use Psr\Http\Message\UriInterface;

class Request
{
    private $url;
    private $begin;
    private $end;

    /**
     * Request constructor.
     * @param $url
     * @param $begin
     * @param $end
     */
    public function __construct(UriInterface $url, int $begin, int $end)
    {
        $this->url = $url;
        $this->begin = $begin;
        $this->end = $end;
    }

    /**
     * @return UriInterface
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function getDuration()
    {
        $duration = $this->end - $this->begin;
        return \DateInterval::createFromDateString($duration . 'ms');
    }
}
