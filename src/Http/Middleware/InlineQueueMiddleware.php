<?php

namespace Jorbascrumps\QueueIt\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Nette\NotImplementedException;

class InlineQueueMiddleware
{
    /**
     * Handle an incoming request.
     * @see https://github.com/queueit/KnownUser.V3.PHP#implementation
     */
    public function handle(Request $request, Closure $next)
    {
        throw new NotImplementedException;
    }
}
