<?php

include_once __DIR__ . "/../vendor/autoload.php";

$httpClient = new \phm\HttpWebdriverClient\Http\Client\Chrome\ChromeClient();

$request = new \GuzzleHttp\Psr7\Request('GET', 'http://www.wunderweib.de');
$response = $httpClient->sendRequest($request);

var_dump($response->getResourceCount('ioam.de/tx.io'));