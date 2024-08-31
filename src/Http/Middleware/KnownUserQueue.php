<?php

namespace Jorbascrumps\QueueIt\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use QueueIT\KnownUserV3\SDK\KnownUser;
use QueueIT\KnownUserV3\SDK\KnownUserException;

class KnownUserQueue
{
    public const TOKEN_KEY = 'queueittoken';

    /**
     * Handle an incoming request.
     * @see https://github.com/queueit/KnownUser.V3.PHP#implementation
     * @throws KnownUserException
     */
    public function handle(Request $request, Closure $next)
    {
        if (! config('queue-it.enabled')) {
            return $next($request);
        }

        $customerId = config('queue-it.customer_id');
        $secretKey = config('queue-it.secret_key');

        $token = $request->query(self::TOKEN_KEY);
        $urlWithoutToken = $request->fullUrlWithoutQuery(self::TOKEN_KEY);

        $configPath = config('queue-it.config_file');
        $config = Storage::get($configPath);

        $result = KnownUser::validateRequestByIntegrationConfig(
            $urlWithoutToken,
            $token,
            $config,
            $customerId,
            $secretKey
        );

        if ($result->doRedirect()) {
            return redirect($result->redirectUrl)->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
            ]);
        }

        if ($result->actionType === 'Queue' && $request->filled(self::TOKEN_KEY)) {
            return redirect($urlWithoutToken);
        }

        return $next($request);
    }
}