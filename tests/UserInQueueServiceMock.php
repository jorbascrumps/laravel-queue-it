<?php

namespace Jorbascrumps\QueueIt\Test;

use Exception;
use QueueIT\KnownUserV3\SDK\ActionTypes;
use QueueIT\KnownUserV3\SDK\CancelEventConfig;
use QueueIT\KnownUserV3\SDK\IUserInQueueService;
use QueueIT\KnownUserV3\SDK\QueueEventConfig;
use QueueIT\KnownUserV3\SDK\RequestValidationResult;

class UserInQueueServiceMock implements IUserInQueueService
{
    public array $arrayFunctionCallsArgs = [
        'validateRequest' => [],
        'extendQueueCookie' => [],
        'validateCancelRequest' => [],
        'getIgnoreActionResult' => [],
    ];

    public array $arrayReturns = [
        'validateRequest' => [],
        'validateCancelRequest' => [],
        'extendQueueCookie' => [],
    ];

    public RequestValidationResult $validateCancelRequestResult;

    public bool $validateCancelRequestRaiseException = false;

    public RequestValidationResult $validateQueueRequestResult;

    public bool $validateQueueRequestRaiseException = false;

    public function validateQueueRequest($currentPageUrl, $queueitToken, QueueEventConfig $config, $customerId, $secretKey): RequestValidationResult
    {
        $this->arrayFunctionCallsArgs['validateRequest'][] = func_get_args();

        if ($this->validateQueueRequestRaiseException) {
            throw new Exception("exception");
        }

        return $this->validateQueueRequestResult;
    }

    public function validateCancelRequest($targetUrl, CancelEventConfig $cancelConfig, $customerId, $secretKey): RequestValidationResult
    {
        $this->arrayFunctionCallsArgs['validateCancelRequest'][] = func_get_args();

        if ($this->validateCancelRequestRaiseException) {
            throw new Exception("exception");
        }

        return $this->validateCancelRequestResult;
    }

    public function getIgnoreActionResult($actionName): RequestValidationResult
    {
        $this->arrayFunctionCallsArgs['getIgnoreActionResult'][] = 'call';

        return new RequestValidationResult(ActionTypes::IgnoreAction, null, null, null, null, $actionName);
    }

    public function extendQueueCookie($eventId, $cookieValidityMinute, $cookieDomain, $isCookieHttpOnly, $isCookieSecure, $secretKey): void
    {
        $this->arrayFunctionCallsArgs['extendQueueCookie'][] = func_get_args();
    }

    public function expectCall($functionName, $sequenceNum, array $argument)
    {
        if (count($this->arrayFunctionCallsArgs[$functionName]) >= $sequenceNum) {
            $argArr = $this->arrayFunctionCallsArgs[$functionName][$sequenceNum - 1];

            if (count($argument) !== count($argArr)) {
                return false;
            }

            for ($i = 0; $i <= count($argArr) - 1; ++$i) {
                if ($argArr[$i] !== $argument[$i]) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    public function expectCallAny($functionName)
    {
        return count($this->arrayFunctionCallsArgs[$functionName]) >= 1;
    }
}
