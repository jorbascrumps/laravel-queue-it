<?php

namespace Jorbascrumps\QueueIt\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jorbascrumps\QueueIt\Events\QueueFailed;
use Jorbascrumps\QueueIt\Events\UserQueued;
use QueueIT\KnownUserV3\SDK\ActionTypes;
use QueueIT\KnownUserV3\SDK\KnownUser;
use QueueIT\KnownUserV3\SDK\KnownUserException;
use QueueIT\KnownUserV3\SDK\QueueEventConfig;
use Stringable;

class InlineQueue implements Stringable
{
    public const ALIAS = 'queue-it.inline-queue';

    public const TOKEN_KEY = 'queueittoken';

    protected ?string $eventId = null;

    protected ?string $queueDomain = null;

    protected ?string $cookieDomain = null;

    protected int $cookieValidityMinute = 15;

    protected bool $extendCookieValidity = true;

    protected ?string $culture = null;

    protected ?string $layoutName = null;

    public function __construct(
        ?string $eventId = null,
        ?string $queueDomain = null,
        ?string $cookieDomain = null,
        int     $cookieValidityMinute = 15,
        bool    $extendCookieValidity = true,
        ?string $culture = null,
        ?string $layoutName = null
    )
    {
        $this->layoutName = $layoutName;
        $this->culture = $culture;
        $this->extendCookieValidity = $extendCookieValidity;
        $this->cookieValidityMinute = $cookieValidityMinute;
        $this->cookieDomain = $cookieDomain;
        $this->queueDomain = $queueDomain;
        $this->eventId = $eventId;
    }

    /**
     * Handle an incoming request.
     * @see https://github.com/queueit/KnownUser.V3.PHP#implementation-using-inline-queue-configuration
     */
    public function handle(Request $request, Closure $next, ...$eventConfigParams)
    {
        $customerId = config('queue-it.customer_id');
        $secretKey = config('queue-it.secret_key');
        $cacheHeaders = config('queue-it.redirect_cache_headers');

        $urlWithoutToken = $request->fullUrlWithoutQuery(self::TOKEN_KEY);
        $token = $request->query(self::TOKEN_KEY);
        $eventConfig = $this->getEventConfig(...$eventConfigParams);

        try {
            $result = KnownUser::resolveQueueRequestByLocalConfig($urlWithoutToken, $token, $eventConfig, $customerId, $secretKey);
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

    public static function eventId(string $eventId): self
    {
        return new self($eventId);
    }

    public function cookieDomain($cookieDomain): self
    {
        $this->cookieDomain = $cookieDomain;

        return $this;
    }

    public function cookieValidityMinute($cookieValidityMinute): self
    {
        $this->cookieValidityMinute = $cookieValidityMinute;

        return $this;
    }

    public function culture($culture): self
    {
        $this->culture = $culture;

        return $this;
    }

    public function extendCookieValidity($extendCookieValidity): self
    {
        $this->extendCookieValidity = $extendCookieValidity;

        return $this;
    }

    public function layoutName($layoutName): self
    {
        $this->layoutName = $layoutName;

        return $this;
    }

    public function queueDomain($queueDomain): self
    {
        $this->queueDomain = $queueDomain;

        return $this;
    }

    private function getEventConfig(
        string $eventId = '',
        string $queueDomain = '',
        string $cookieDomain = '',
        int    $cookieValidityMinute = 15,
        bool   $extendCookieValidity = true,
        string $culture = '',
        string $layoutName = ''
    ): QueueEventConfig
    {
        $config = new QueueEventConfig;
        $config->eventId = $eventId;
        $config->cookieValidityMinute = $cookieValidityMinute;
        $config->extendCookieValidity = $extendCookieValidity;
        $config->queueDomain = $queueDomain;

        if ($cookieDomain) {
            $config->cookieDomain = $cookieDomain;
        }

        if ($culture) {
            $config->culture = $culture;
        }

        if ($layoutName) {
            $config->layoutName = $layoutName;
        }

        return $config;
    }

    public function __toString(): string
    {
        $params = collect([
            $this->eventId,
            $this->queueDomain,
            $this->cookieDomain,
            $this->cookieValidityMinute,
            $this->extendCookieValidity,
            $this->culture,
            $this->layoutName,
        ])
            ->map(fn ($value) => $value ?? '')
            ->implode(',');

        return self::ALIAS . ':' . $params;
    }
}
