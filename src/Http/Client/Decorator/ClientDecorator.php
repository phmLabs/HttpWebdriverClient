<?php
/**
 * Created by PhpStorm.
 * User: nils.langner
 * Date: 26.11.17
 * Time: 05:52
 */

namespace phm\HttpWebdriverClient\Http\Client\Decorator;


interface ClientDecorator
{
    public function getClient();
}