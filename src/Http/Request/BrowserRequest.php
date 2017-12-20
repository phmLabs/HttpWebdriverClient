<?php

namespace phm\HttpWebdriverClient\Http\Request;

use GuzzleHttp\Psr7\Request;
use phm\HttpWebdriverClient\Http\Request\Device\Device;
use phm\HttpWebdriverClient\Http\Request\Device\DefaultDevice;

class BrowserRequest extends Request implements DeviceAwareRequest, CacheAwareRequest
{
    private $viewport;
    private $userAgent;

    private $device;

    public function __construct($method, $uri, array $headers = [], $body = null, $version = '1.1')
    {
        parent::__construct($method, $uri, $headers, $body, $version);
        $this->setDevice(new DefaultDevice());
    }

    public function setDevice(Device $device)
    {
        $this->setViewport($device->getViewport());
        $this->setUserAgent($device->getUserAgent());
        $this->device = $device;
    }

    /**
     * @return Device
     */
    public function getDevice()
    {
        return $this->device;
    }

    public function setViewport(Viewport $viewport)
    {
        $this->viewport = $viewport;
    }

    public function getViewport()
    {
        return $this->viewport;
    }

    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }

    public function getHash()
    {
        return md5(
            $this->getMethod() . '-' .
            (string)$this->getUri() . '-' .
            json_encode($this->getHeaders()) . '-' .
            $this->getUserAgent() . '-' .
            json_encode($this->getViewport()->jsonSerialize())
        );
    }
}
