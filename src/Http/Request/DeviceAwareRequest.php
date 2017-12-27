<?php

namespace phm\HttpWebdriverClient\Http\Request;

use Leankoala\Devices\Device;
use Psr\Http\Message\RequestInterface;

interface DeviceAwareRequest extends RequestInterface, UserAgentAwareRequest, ViewportAwareRequest
{
    /**
     * @param Device $userAgent
     */
    public function setDevice(Device $device);

    /**
     * @param Device $device
     * @return Device
     */
    public function getDevice();
}