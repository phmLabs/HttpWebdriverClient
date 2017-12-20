<?php

namespace phm\HttpWebdriverClient\Http\Request\Device;

class DeviceFactory
{
    public static function create($deviceName)
    {
        $class = __NAMESPACE__ . '\\Devices\\' . $deviceName;

        if (class_exists($class)) {
            return new $class();

        } else {
            throw new \RuntimeException('The given device was not found (' . $class . ')');
        }
    }
}