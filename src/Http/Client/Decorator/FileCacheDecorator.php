<?php

namespace phm\HttpWebdriverClient\Http\Client\Decorator;

use Cache\Adapter\Filesystem\FilesystemCachePool;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use phm\HttpWebdriverClient\Http\Client\HttpClient;

class FileCacheDecorator extends CacheDecorator implements ClientDecorator
{
    const CACHE_DIRECTORY_DEFAULT = '/tmp/cache/http/';

    /**
     * FileCacheDecorator constructor.
     * @param HttpClient $client
     * @param null $cacheDirectory
     * @param null $expiresAfter
     * @throws \Exception
     */
    public function __construct(HttpClient $client, $cacheDirectory = null, $expiresAfter = null)
    {
        if (!$cacheDirectory) {
            $cacheDirectory = self::CACHE_DIRECTORY_DEFAULT;
        }

        if (!file_exists($cacheDirectory)) {
            mkdir($cacheDirectory, 0777, true);
        }

        if (!$expiresAfter) {
            $expiresAfter = new \DateInterval('PT45M');
        }

        $filesystemAdapter = new Local($cacheDirectory);
        $filesystem = new Filesystem($filesystemAdapter);
        $cachePoolInterface = new FilesystemCachePool($filesystem);

        parent::__construct($client, $cachePoolInterface, $expiresAfter);
    }
}
