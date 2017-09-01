<?php

namespace phm\HttpWebdriverClient\Http\Client\Decorator;

use Cache\Adapter\Filesystem\FilesystemCachePool;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use phm\HttpWebdriverClient\Http\Client\HttpClient;

class FileCacheDecorator extends CacheDecorator
{
    const CACHE_DIRECTORY_DEFAULT = '/tmp/cache/http/';

    public function __construct(HttpClient $client, $cacheDirectory = null, $expiresAfter = null)
    {
        if (!$cacheDirectory) {
            $cacheDirectory = self::CACHE_DIRECTORY_DEFAULT;
        }

        $filesystemAdapter = new Local($cacheDirectory);
        $filesystem = new Filesystem($filesystemAdapter);
        $cachePoolInterface = new FilesystemCachePool($filesystem);

        parent::__construct($client, $cachePoolInterface, $expiresAfter);
    }
}
