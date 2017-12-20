<?php

namespace phm\HttpWebdriverClient\Http\Request\Device\Devices;

use phm\HttpWebdriverClient\Http\Request\Device\AbstractDevice;

class IPhone8Landscape extends AbstractDevice
{
    protected $name = 'Apple iPhone 8 (landscape)';

    protected $userAgent = "Mozilla/5.0 (iPhone; CPU OS 11_0 like Mac OS X) AppleWebKit/604.1.25 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1";

    protected $viewport = [
        "width" => 667,
        "height" => 375,
        "isMobile" => true,
        "hasTouch" => true,
        "isLandscape" => true
    ];
}
