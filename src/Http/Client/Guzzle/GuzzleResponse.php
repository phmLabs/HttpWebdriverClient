<?php

namespace phm\HttpWebdriverClient\Http\Client\Guzzle;

use phm\HttpWebdriverClient\Http\Response\ContentLengthAwareResponse;
use phm\HttpWebdriverClient\Http\Response\ContentTypeAwareResponse;
use phm\HttpWebdriverClient\Http\Response\DurationAwareResponse;
use phm\HttpWebdriverClient\Http\Response\EffectiveUriAwareResponse;
use phm\HttpWebdriverClient\Http\Response\RequestAwareResponse;
use phm\HttpWebdriverClient\Http\Response\ResourcesAwareResponse;
use phm\HttpWebdriverClient\Http\Response\UriAwareResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use whm\Html\Document;

// @todo at the moment all with-methods are not working as they do not decorate the response

class GuzzleResponse implements DurationAwareResponse, ContentTypeAwareResponse, UriAwareResponse, ResourcesAwareResponse, EffectiveUriAwareResponse, RequestAwareResponse, ContentLengthAwareResponse
{
    private $response;
    private $request;
    private $uri;
    private $duration;

    /**
     * @var UriInterface
     */
    private $effectiveUri;

    private $uncompressedBody;

    public function __construct(ResponseInterface $response, RequestInterface $request = null)
    {
        $this->response = $response;
        $this->request = $request;
    }

    /**
     * @param RequestInterface $request
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function setEffectiveUri(UriInterface $effectiveUri)
    {
        $this->effectiveUri = $effectiveUri;
    }

    public function getProtocolVersion()
    {
        return $this->response->getProtocolVersion();
    }

    public function withProtocolVersion($version)
    {
        return $this->response->withProtocolVersion($version);
    }

    public function getHeaders()
    {
        return $this->response->getHeaders();
    }

    public function hasHeader($name)
    {
        return $this->response->hasHeader($name);
    }

    public function getHeader($name)
    {
        return $this->response->getHeader($name);
    }

    public function getHeaderLine($name)
    {
        return $this->response->getHeaderLine($name);
    }

    public function withHeader($name, $value)
    {
        return $this->response->withHeader($name, $value);
    }

    public function withAddedHeader($name, $value)
    {
        return $this->response->withAddedHeader($name, $value);
    }

    public function withoutHeader($name)
    {
        return $this->response->withoutHeader($name);
    }

    public function getBody()
    {
        if ($this->uncompressedBody) {
            return $this->uncompressedBody;
        }

        $body = @gzdecode((string)$this->response->getBody());
        if (!$body) {
            $body = $this->response->getBody();
        }

        $this->uncompressedBody = $body;
        return $body;
    }

    public function setBody($body)
    {
        $this->uncompressedBody = $body;
        $this->response->withBody(\GuzzleHttp\Psr7\stream_for($body));
    }

    /**
     * Return the plain body although it is compressed
     *
     * @return \GuzzleHttp\Psr7\Stream|StreamInterface
     */
    public function getPlainBody()
    {
        return $this->response->getBody();
    }

    public function withBody(StreamInterface $body)
    {
        return $this->response->withBody($body);
    }

    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        return $this->response->withStatus($code, $reasonPhrase);
    }

    public function getReasonPhrase()
    {
        return $this->response->getReasonPhrase();
    }

    public function setUri(UriInterface $uri)
    {
        $this->uri = $uri;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getContentType()
    {
        if ($this->hasHeader('Content-Type')) {
            $exploded = explode(';', $this->getHeader('Content-Type')[0]);
            return array_shift($exploded);
        } else {
            return '';
        }
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function getResources()
    {
        if (strpos($this->getContentType(), 'json') !== false) {
            return [];
        }

        $htmlDocument = new Document((string)$this->getBody());
        $plainResources = $htmlDocument->getDependencies($this->getUri()->withPath(''), false);

        $resources = [];
        foreach ($plainResources as $plainResource) {
            $resources[] = ['name' => $plainResource, 'transferSize' => 0];
        }

        return $resources;
    }

    public function getResourceCount($pattern)
    {
        $count = 0;
        $resources = $this->getResources();
        foreach ($resources as $resource) {
            $element = (string)$resource['name'];
            if (preg_match('^' . $pattern . '^', $element)) {
                $count++;
            }
        }
        return $count;
    }

    public function getContentLength()
    {
        return strlen((string)$this->response->getBody());
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function getEffectiveUri()
    {
        if ($this->effectiveUri) {
            return $this->effectiveUri;
        } else {
            return $this->getUri();
        }
    }
}
