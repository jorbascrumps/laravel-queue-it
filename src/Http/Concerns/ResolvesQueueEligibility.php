<?php

namespace Jorbascrumps\QueueIt\Http\Concerns;

use Illuminate\Container\Container;

trait ResolvesQueueEligibility
{
    /**
     * The callback that is responsible for resolving user queue eligibility.
     * @var callable|null
     */
    protected static $userQueueEligibilityResolver;

    /**
     * Register a callback that is responsible for resolving user queue eligibility.
     */
    public static function resolveUserQueueEligibilityUsing(callable $callback): void
    {
        static::$userQueueEligibilityResolver = $callback;
    }

    /**
     * Resolve user queue eligibility.
     */
    protected function resolveUserQueueEligibility(): bool
    {
        if (isset(static::$userQueueEligibilityResolver)) {
            return Container::getInstance()->call(self::$userQueueEligibilityResolver);
        }

        return true;
    }
}
