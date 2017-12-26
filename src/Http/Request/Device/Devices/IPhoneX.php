<?php

namespace phm\HttpWebdriverClient\Http\Request\Device\Devices;

use phm\HttpWebdriverClient\Http\Request\Device\AbstractDevice;

class IPhoneX extends AbstractDevice
{
    protected $name = 'Apple iPhone X';

    protected $userAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1';

    protected $viewport = [
        "width" => 375,
        "height" => 812,
        "isMobile" => true,
        "hasTouch" => true,
        'isLandscape' => false,
        'deviceScaleFactor' => 3
    ];
}
