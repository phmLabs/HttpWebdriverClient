<?php

namespace phm\HttpWebdriverClient\Http\Request\Device;

/**
 * Interface Device
 *
 * A list of (older) standard devices can be found within the puppeteer project
 *
 * @link https://github.com/GoogleChrome/puppeteer/blob/master/DeviceDescriptors.js
 *
 * @package phm\HttpWebdriverClient\Http\Request\Device
 */
interface Device
{
    public function getViewport();

    public function getUserAgent();

    public function getName();
}