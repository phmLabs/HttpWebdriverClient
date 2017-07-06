<?php


namespace phm\HttpWebdriverClient\Http\Client\Chrome;

use phm\HttpWebdriverClient\Http\Response\DetailedResponse;
use phm\HttpWebdriverClient\Http\Response\EffectiveUriAwareResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class ChromeResponse implements EffectiveUriAwareResponse, DetailedResponse, \JsonSerializable
{
    private $statusCode;
    private $body;
    private $headers = [];
    private $protocolVersion = null;
    private $resources = [];
    private $request;
    private $duration;
    private $effectiveUri;

    private function normalizeHeaders($headers = [])
    {
        $normalizedHeaders = [];

        foreach ($headers as $key => $value) {
            $normalizedHeaders[strtolower($key)] = $value;
        }

        return $normalizedHeaders;
    }

    /**
     * ChromeResponse constructor.
     * @param $statusCode
     * @param string $body
     * @param RequestInterface $request
     * @param array $resources
     * @param array $headers
     */
    public function __construct($statusCode, $body = "", RequestInterface $request, array $resources, array $headers, UriInterface $effectiveUri)
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->headers = $this->normalizeHeaders($headers);
        $this->request = $request;
        $this->resources = $resources;
        $this->effectiveUri = $effectiveUri;
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
        return array_key_exists(strtolower($name), $this->headers);
    }

    public function getHeader($name)
    {
        if ($this->hasHeader($name)) {
            return [$this->headers[strtolower($name)]];
        } else {
            throw new \RuntimeException('Header with name "' . $name . '" not found.');
        }
    }

    public function getHeaderLine($name)
    {
        if ($this->hasHeader($name)) {
            return $this->headers[strtolower($name)];
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
        return new self($this->statusCode, $this->body, $this->request, $this->resources, $headers);
    }

    public function withoutHeader($name)
    {
        $headers = $this->headers;

        if ($this->hasHeader($name)) {
            unset($headers[$name]);
        }

        return new self($this->statusCode, $this->body, $this->request, $this->resources, $headers);
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getPlainBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function withBody(StreamInterface $body)
    {
        return new self($this->statusCode, (string)$body, $this->request, $this->resources, $this->headers);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        return new self($code, $this->body, $this->request, $this->resources, $this->headers);
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

    public function getResourceCount($pattern)
    {
        $foundCount = 0;

        foreach ($this->resources as $request) {
            $url = $request['name'];
            if (preg_match('`' . $pattern . '`', $url)) {
                ++$foundCount;
            }
        }

        return $foundCount;
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

    public function getUri()
    {
        return $this->getRequest()->getUri();
    }

    public function getContentType()
    {
        if ($this->hasHeader('content-type')) {
            $exploded = explode(';', $this->getHeader('content-type')[0]);
            return array_shift($exploded);
        } else {
            return '';
        }
    }

    public function getEffectiveUri()
    {
        return $this->effectiveUri;
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