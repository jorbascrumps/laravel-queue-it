<?php

namespace Jorbascrumps\QueueIt\Http\Middleware;

use BadMethodCallException;
use Closure;
use Illuminate\Http\Request;
use Jorbascrumps\QueueIt\Events\QueueFailed;
use Jorbascrumps\QueueIt\Events\UserQueued;
use Jorbascrumps\QueueIt\Http\Concerns\ResolvesQueueEligibility;
use QueueIT\KnownUserV3\SDK\ActionTypes;
use QueueIT\KnownUserV3\SDK\KnownUser;
use QueueIT\KnownUserV3\SDK\KnownUserException;
use QueueIT\KnownUserV3\SDK\QueueEventConfig;
use ReflectionClass;
use Stringable;

/**
 * @method static self eventId(string $eventId)
 * @method static self queueDomain(string $queueDomain)
 * @method static self cookieDomain(string $cookieDomain)
 * @method static self cookieValidityMinute(int $cookieValidityMinute)
 * @method static self extendCookieValidity(bool $extendCookieValidity)
 * @method static self culture(string $culture)
 * @method static self layoutName(string $layoutName)
 *
 * @method self eventId(string $eventId)
 * @method self queueDomain(string $queueDomain)
 * @method self cookieDomain(string $cookieDomain)
 * @method self cookieValidityMinute(int $cookieValidityMinute)
 * @method self extendCookieValidity(bool $extendCookieValidity)
 * @method self culture(string $culture)
 * @method self layoutName(string $layoutName)
 */
class InlineQueue implements Stringable
{
    use ResolvesQueueEligibility;

    public const ALIAS = 'queue-it.inline-queue';

    public const TOKEN_KEY = 'queueittoken';

    public function __construct(
        protected ?string $eventId = null,
        protected ?string $queueDomain = null,
        protected ?string $cookieDomain = null,
        protected int $cookieValidityMinute = 15,
        protected bool $extendCookieValidity = true,
        protected ?string $culture = null,
        protected ?string $layoutName = null,
    )
    {
        //
    }

    /**
     * Handle an incoming request.
     * @see https://github.com/queueit/KnownUser.V3.PHP#implementation-using-inline-queue-configuration
     */
    public function handle(Request $request, Closure $next, ...$eventConfigParams)
    {
        if (! $this->resolveUserQueueEligibility()) {
            return $next($request);
        }

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

    protected function setParameter(string $name, $value): self
    {
        $this->$name = $value;

        return $this;
    }

    public static function __callStatic($name, $arguments)
    {
        if (self::isValidParameter($name)) {
            return (new self)->setParameter($name, $arguments[0]);
        }

        throw new BadMethodCallException("Parameter $name does not exist.");
    }

    public function __call($name, $arguments)
    {
        if (self::isValidParameter($name)) {
            return $this->setParameter($name, $arguments[0]);
        }

        throw new BadMethodCallException("Parameter $name does not exist.");
    }

    private static function isValidParameter(string $name): bool
    {
        static $params = null;

        if ($params === null) {
            $reflectionClass = new ReflectionClass(self::class);
            $reflectionConstructor = $reflectionClass->getConstructor();
            $params = collect($reflectionConstructor->getParameters())
                ->pluck('name');
        }

        return $params->contains($name);
    }
}
