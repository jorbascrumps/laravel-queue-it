<?php

namespace Jorbascrumps\QueueIt\Http\Middleware;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Jorbascrumps\QueueIt\Events\QueueFailed;
use Jorbascrumps\QueueIt\Events\UserQueued;
use Jorbascrumps\QueueIt\HttpRequestProvider;
use QueueIT\KnownUserV3\SDK\ActionTypes;
use QueueIT\KnownUserV3\SDK\KnownUser;
use QueueIT\KnownUserV3\SDK\KnownUserException;

class KnownUserQueue
{
    public const ALIAS = 'queue-it.known-user-queue';

    public const TOKEN_KEY = 'queueittoken';

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

    /**
     * Handle an incoming request.
     * @see https://github.com/queueit/KnownUser.V3.PHP#implementation
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $this->resolveUserQueueEligibility()) {
            return $next($request);
        }

        $customerId = config('queue-it.customer_id');
        $secretKey = config('queue-it.secret_key');
        $cacheHeaders = config('queue-it.redirect_cache_headers');

        $token = $request->query(self::TOKEN_KEY);
        $urlWithoutToken = $request->fullUrlWithoutQuery(self::TOKEN_KEY);

        $configPath = config('queue-it.config_file');
        $config = Storage::get($configPath);

        KnownUser::setHttpRequestProvider(new HttpRequestProvider($request));

        try {
            $result = KnownUser::validateRequestByIntegrationConfig($urlWithoutToken, $token, $config, $customerId, $secretKey);
        } catch (KnownUserException $e) {
            event(new QueueFailed($e));

            $header = config('queue-it.queue_error_header');

            return $next($request)->header($header, true);
        }

        if ($result->doRedirect()) {
            event(new UserQueued($result));

            return redirect($result->redirectUrl)->setCache($cacheHeaders);
        }

        if ($result->actionType === ActionTypes::QueueAction && $request->filled(self::TOKEN_KEY)) {
            return redirect($urlWithoutToken);
        }

        return $next($request);
    }
}
