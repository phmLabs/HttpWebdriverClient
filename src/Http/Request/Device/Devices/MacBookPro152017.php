<?php

namespace phm\HttpWebdriverClient\Http\Request\Device\Devices;

use phm\HttpWebdriverClient\Http\Request\Device\AbstractDevice;

class MacBookPro152017 extends AbstractDevice
{
    protected $name = 'MacBook Pro 15" (2017)';

    protected $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36';

    protected $viewport = [
        "width" => 2880,
        "height" => 1800,
        "isMobile" => false,
        "hasTouch" => false,
        'isLandscape' => false
    ];
}
