<?php

include_once __DIR__ . "/../vendor/autoload.php";

$httpClient = new \phm\HttpWebdriverClient\Http\HttpAdapter();

$request = new \GuzzleHttp\Psr7\Request('GET', 'http://www.wunderweib.de');
$response = $httpClient->sendRequest($request);

var_dump($response->hasRequest('ioam.de/tx.io'));