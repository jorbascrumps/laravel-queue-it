<?php

namespace Jorbascrumps\QueueIt\Events;

use QueueIT\KnownUserV3\SDK\KnownUserException;

class QueueFailed
{
    public KnownUserException $exception;

    /**
     * Create a new event instance.
     */
    public function __construct(KnownUserException $exception)
    {
        $this->exception = $exception;
    }
}
