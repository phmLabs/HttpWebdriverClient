<?php

namespace phm\HttpWebdriverClient\Http\Request\Device\Devices;

use phm\HttpWebdriverClient\Http\Request\Device\AbstractDevice;

class IPhone8 extends AbstractDevice
{
    protected $name = 'Apple iPhone 8';

    protected $userAgent = "Mozilla/5.0 (iPhone; CPU OS 11_0 like Mac OS X) AppleWebKit/604.1.25 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1";

    protected $viewport = [
        "width" => 375,
        "height" => 667,
        "isMobile" => true,
        "hasTouch" => true
    ];
}
