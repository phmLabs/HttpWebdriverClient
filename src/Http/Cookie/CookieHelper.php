<?php

namespace phm\HttpWebdriverClient\Http\Cookie;

abstract class CookieHelper
{
    /**
     * The default http cookie name
     */
    const HEADER_NAME = 'cookie';

    /**
     * Convert a key value array to a valid cookie string
     *
     * @param array $cookieArray
     * @return string
     */
    public static function toCookieString(array $cookieArray = [])
    {
        $cookieString = "";

        foreach ($cookieArray as $key => $value) {
            $cookieString .= $key . '=' . $value . '; ';
        }

        return $cookieString;
    }

    /**
     * Merge to cookie strings
     *
     * @param $string1
     * @param $string2
     * @return string
     */
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