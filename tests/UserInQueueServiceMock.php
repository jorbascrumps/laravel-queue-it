<?php

namespace Jorbascrumps\QueueIt\Test;

use QueueIT\KnownUserV3\SDK\IUserInQueueService;
use QueueIT\KnownUserV3\SDK\RequestValidationResult;

class UserInQueueServiceMock implements IUserInQueueService
{
    public RequestValidationResult $validateQueueRequestResult;

    public function validateQueueRequest($currentPageUrl, $queueitToken, $config, $customerId, $secretKey): RequestValidationResult
    {
        return $this->validateQueueRequestResult;
    }

    public function validateCancelRequest($targetUrl, $cancelConfig, $customerId, $secretKey)
    {
        //
    }

    public function getIgnoreActionResult($actionName)
    {
        //
    }

    public function extendQueueCookie($eventId, $cookieValidityMinutes, $cookieDomain, $isCookieHttpOnly, $isCookieSecure, $secretKey)
    {
        //
    }
}
