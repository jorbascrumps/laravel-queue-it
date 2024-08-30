<?php

namespace Jorbascrumps\QueueIt\Test;

use QueueIT\KnownUserV3\SDK\IHttpRequestProvider;

class HttpRequestProviderMock implements IHttpRequestProvider
{
    public $userAgent;

    public $userHostAddress;

    public $cookieManager;

    public $absoluteUri;

    public $headerArray;

    public $requestBody;

    public function getUserAgent()
    {
        return $this->userAgent;
    }

    public function getUserHostAddress()
    {
        return $this->userHostAddress;
    }

    public function getCookieManager()
    {
        return $this->cookieManager;
    }

    public function getAbsoluteUri()
    {
        return $this->absoluteUri;
    }

    public function getHeaderArray(): array
    {
        return $this->headerArray ?? [];
    }

    public function getRequestBodyAsString()
    {
        return $this->requestBody;
    }
}
