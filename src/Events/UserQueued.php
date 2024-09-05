<?php

namespace Jorbascrumps\QueueIt\Events;

use QueueIT\KnownUserV3\SDK\RequestValidationResult;

class UserQueued
{
    public RequestValidationResult $requestValidationResult;

    /**
     * Create a new event instance.
     */
    public function __construct(RequestValidationResult $requestValidationResult)
    {
        $this->requestValidationResult = $requestValidationResult;
    }
}
