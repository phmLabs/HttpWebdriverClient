<?php

namespace phm\HttpWebdriverClient\Http\Client\HeadlessChrome;

use phm\HttpWebdriverClient\Http\Response\DetailedResponse;
use phm\HttpWebdriverClient\Http\Response\EffectiveUriAwareResponse;
use phm\HttpWebdriverClient\Http\Response\JavaScriptAwareResponse;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class HeadlessChromeResponse implements EffectiveUriAwareResponse, DetailedResponse, JavaScriptAwareResponse, \JsonSerializable
{
    public function getContentType()
    {
        // TODO: Implement getContentType() method.
    }

    public function getDuration()
    {
        // TODO: Implement getDuration() method.
    }

    public function getEffectiveUri()
    {
        // TODO: Implement getEffectiveUri() method.
    }

    public function getJavaScriptErrors()
    {
        // TODO: Implement getJavaScriptErrors() method.
    }

    public function getProtocolVersion()
    {
        // TODO: Implement getProtocolVersion() method.
    }

    public function withProtocolVersion($version)
    {
        // TODO: Implement withProtocolVersion() method.
    }

    public function getHeaders()
    {
        // TODO: Implement getHeaders() method.
    }

    public function hasHeader($name)
    {
        // TODO: Implement hasHeader() method.
    }

    public function getHeader($name)
    {
        // TODO: Implement getHeader() method.
    }

    public function getHeaderLine($name)
    {
        // TODO: Implement getHeaderLine() method.
    }

    public function withHeader($name, $value)
    {
        // TODO: Implement withHeader() method.
    }

    public function withAddedHeader($name, $value)
    {
        // TODO: Implement withAddedHeader() method.
    }

    public function withoutHeader($name)
    {
        // TODO: Implement withoutHeader() method.
    }

    public function getBody()
    {
        // TODO: Implement getBody() method.
    }

    public function withBody(StreamInterface $body)
    {
        // TODO: Implement withBody() method.
    }

    public function getResources()
    {
        // TODO: Implement getResources() method.
    }

    public function getResourceCount($pattern)
    {
        // TODO: Implement getResourceCount() method.
    }

    public function getStatusCode()
    {
        // TODO: Implement getStatusCode() method.
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        // TODO: Implement withStatus() method.
    }

    public function getReasonPhrase()
    {
        // TODO: Implement getReasonPhrase() method.
    }

    public function getUri()
    {
        // TODO: Implement getUri() method.
    }

    function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }

}