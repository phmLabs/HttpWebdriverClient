<?php

namespace phm\HttpWebdriverClient\Http\Request\Device;

use phm\HttpWebdriverClient\Http\Request\Viewport;

abstract class AbstractDevice implements Device
{
    protected $viewport = [];

    private $defaultViewport = [
        "isMobile" => false,
        "hasTouch" => false,
        "isLandscape" => false,
        'deviceScaleFactor' => 1
    ];

    protected $userAgent = "";

    protected $name = '';

    public function getViewport()
    {
        $viewport = array_merge($this->defaultViewport, $this->viewport);

        return new Viewport(
            $viewport['height'],
            $viewport['width'],
            $viewport['isMobile'],
            $viewport['hasTouch'],
            $viewport['isLandscape'],
            $viewport['deviceScaleFactor']);
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }

    public function getName()
    {
        return $this->name;
    }
}
