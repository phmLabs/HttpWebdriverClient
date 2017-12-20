<?php

namespace phm\HttpWebdriverClient\Http\Request;

class Viewport implements \JsonSerializable
{
    private $height;
    private $width;

    private $isLandscape = false;
    private $isMobile = false;
    private $hasTouch = false;
    private $deviceScaleFactor = 1;

    public function __construct($height, $width, $isMobile = false, $hasTouch = false, $isLandscape = false, $deviceScaleFactor = 1)
    {
        $this->width = $width;
        $this->height = $height;

        $this->isMobile = $isMobile;
        $this->hasTouch = $hasTouch;
        $this->isLandscape = $isLandscape;

        $this->deviceScaleFactor = $deviceScaleFactor;
    }

    /**
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return bool
     */
    public function isLandscape()
    {
        return $this->isLandscape;
    }

    /**
     * @return bool
     */
    public function isMobile()
    {
        return $this->isMobile;
    }

    /**
     * @return bool
     */
    public function hasTouch()
    {
        return $this->hasTouch;
    }

    /**
     * @return int
     */
    public function getDeviceScaleFactor()
    {
        return $this->deviceScaleFactor;
    }

    public function jsonSerialize()
    {
        return [
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'hasTouch' => $this->hasTouch(),
            'isMobile' => $this->isMobile(),
            'isLandscape' => $this->isLandscape(),
            'deviceScaleFactor' => $this->getDeviceScaleFactor()
        ];
    }
}
