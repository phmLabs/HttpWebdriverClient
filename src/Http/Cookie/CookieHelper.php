<?php

namespace phm\HttpWebdriverClient\Http\Cookie;

abstract class CookieHelper
{
    const HEADER_NAME = 'cookie';

    public static function toCookieString(array $cookieArray = [])
    {
        $cookieString = "";

        foreach ($cookieArray as $key => $value) {
            $cookieString .= $key . '=' . $value . '; ';
        }

        return $cookieString;
    }

    public static function mergeCookieStrings($string1, $string2)
    {
        if ($string1 == '') {
            return $string2;
        }

        if ($string2 == '') {
            return $string1;
        }

        return $string1 . '; ' . $string2;
    }
}