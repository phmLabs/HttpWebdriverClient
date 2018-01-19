<?php

namespace phm\HttpWebdriverClient\Http\Request;

use GuzzleHttp\Psr7\Request;
use Leankoala\Devices\Device;
use Leankoala\Devices\DeviceFactory;
use Leankoala\Devices\Viewport;
use phm\HttpWebdriverClient\Http\Cookie\CookieHelper;

class BrowserRequest extends Request implements DeviceAwareRequest, CacheAwareRequest, CookieAwareRequest
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    const DEFAULT_DEVICE = 'MacBookPro152017';

    private $viewport;
    private $userAgent;

    private $device;
    private $allowCache = true;

    private $cookies = [];

    public function __construct($method, $uri, array $headers = [], $body = null, $version = '1.1')
    {
        parent::__construct($method, $uri, $headers, $body, $version);

        $factory = new DeviceFactory();
        $defaultDevice = $factory->create(self::DEFAULT_DEVICE);
        $this->setDevice($defaultDevice);
    }

    public function setDevice(Device $device)
    {
        $this->setViewport($device->getViewport());
        $this->setUserAgent($device->getUserAgent());
        $this->device = $device;
    }

    /**
     * @return Device
     */
    public function getDevice()
    {
        return $this->device;
    }

    public function setViewport(Viewport $viewport)
    {
        $this->viewport = $viewport;
    }

    public function getViewport()
    {
        return $this->viewport;
    }

    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param array $cookies
     * @return BrowserRequest
     */
    public function withCookies(array $cookies)
    {
        $currentCookieHeader = $this->getHeader(CookieHelper::HEADER_NAME);

        if (count($currentCookieHeader) > 0) {
            $cookieString = CookieHelper::mergeCookieStrings(CookieHelper::toCookieString($cookies), $currentCookieHeader);
        } else {
            $cookieString = CookieHelper::toCookieString($cookies);
        }

        $new = $this->withAddedHeader(CookieHelper::HEADER_NAME, $cookieString);

        /** @var self $new */
        $new->setCookies($cookies);

        return $new;
    }

    private function setCookies(array $cookies)
    {
        $this->cookies = $cookies;
    }

    /**
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    public function getHash()
    {
        $headers = $this->getHeaders();

        if (array_key_exists('Host', $headers) && count($headers['Host']) > 1) {
            $headers['Host'] = [$headers['Host'][0]];
        }

        $identifier = $this->getMethod() . '-' .
            (string)$this->getUri() . '-' .
            json_encode($headers) . '-' .
            $this->getUserAgent() . '-' .
            json_encode($this->getViewport()->jsonSerialize());

        return md5($identifier);
    }

    public function setIsCacheAllowed($isAllowed)
    {
        $this->allowCache = $isAllowed;
    }

    public function isCacheAllowed()
    {
        return $this->allowCache;
    }
}
