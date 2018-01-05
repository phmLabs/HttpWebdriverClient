<?php

namespace phm\HttpWebdriverClient\Http\Request\Device;

class DeviceFactory
{
    /**
     * @param $deviceName
     * @return Device
     */
    public static function create($deviceName)
    {
        if (!$deviceName) {
            return new DefaultDevice();
        }

        $class = __NAMESPACE__ . '\\Devices\\' . $deviceName;

        if (class_exists($class)) {
            return new $class();
        } else {
            throw new \RuntimeException('The given device was not found (' . $class . ')');
        }
    }
}