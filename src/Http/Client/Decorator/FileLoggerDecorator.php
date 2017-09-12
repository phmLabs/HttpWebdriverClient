<?php

namespace phm\HttpWebdriverClient\Http\Client\Decorator;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use phm\HttpWebdriverClient\Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

class FileLoggerDecorator extends LoggerDecorator
{
    const DEFAULT_LOG_FILE = '/tmp/log/leankoala.log';

    public function __construct(HttpClient $client, $loggerName, $fileName = null)
    {
        if (!$fileName) {
            $fileName = self::DEFAULT_LOG_FILE;
        }

        $logger = new Logger($loggerName . '::' . md5(microtime()));
        $logger->pushHandler(new StreamHandler($fileName, Logger::INFO));

        parent::__construct($client, $logger);
    }
}
