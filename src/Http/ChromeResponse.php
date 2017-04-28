<?php

namespace phm\HttpWebdriverClient\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ChromeResponse implements ResourcesAwareResponse, DurationAwareResponse, \JsonSerializable
{
    private $statusCode;
    private $body;
    private $headers = [];
    private $protocolVersion = null;
    private $resources = [];
    private $request;
    private $duration;

    /**
     * ChromeResponse constructor.
     * @param $statusCode
     * @param string $body
     * @param RequestInterface $request
     * @param array $resources
     * @param array $headers
     */
    public function __construct($statusCode, $body = "", RequestInterface $request, array $resources, array $headers)
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->headers = $headers;
        $this->request = $request;
        $this->resources = $resources;
    }

    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    public function setProtocolVersion($version)
    {
        $this->protocolVersion = $version;
    }

    public function withProtocolVersion($version)
    {
        $new = clone $this;
        $new->setProtocolVersion($version);
        return $new;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function hasHeader($name)
    {
        return array_key_exists($name, $this->headers);
    }

    public function getHeader($name)
    {
        if ($this->getHeader($name)) {
            return $this->headers['name'];
        } else {
            throw new \RuntimeException('Header with name "' . $name . '" not found.');
        }
    }

    public function getHeaderLine($name)
    {
        if ($this->hasHeader($name)) {
            return $this->headers[$name];
        } else {
            throw new \RuntimeException('Header with name "' . $name . '" not found.');
        }
    }

    public function withHeader($name, $value)
    {
        return $this->withAddedHeader($name, $value);
    }

    public function withAddedHeader($name, $value)
    {
        $headers = array_merge($this->headers, [$name => $value]);
        return new self($this->statusCode, $this->body, $headers);
    }

    public function withoutHeader($name)
    {
        $headers = $this->headers;

        if ($this->hasHeader($name)) {
            unset($headers[$name]);
        }

        return new self($this->statusCode, $this->body, $headers);
    }

    public function getBody()
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body)
    {
        return new self($this->statusCode, (string)$body, $this->headers);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        return new self($code, $this->body, $this->headers);
    }

    public function getReasonPhrase()
    {
        return "No reason phrase given. Status Code: " . $this->statusCode;
    }

    public function setResources($resources)
    {
        $this->resources = $resources;
    }

    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    function jsonSerialize()
    {
        return [
            'duration' => $this->getDuration(),
            'headers' => $this->getHeaders(),
            'protocolVersion' => $this->getProtocolVersion(),
            'statusCode' => $this->getStatusCode()
        ];
    }
}