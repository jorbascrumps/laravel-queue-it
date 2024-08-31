<?php

namespace Jorbascrumps\QueueIt;

use Illuminate\Routing\Router;
use Jorbascrumps\QueueIt\Http\Middleware\InlineQueue;
use Jorbascrumps\QueueIt\Http\Middleware\KnownUserQueue;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot(Router $router): void
    {
        $this->publishes([
            __DIR__ . '/config/queue-it.php' => config_path('queue-it.php'),
        ]);

        $router->aliasMiddleware(InlineQueue::SIGNATURE, InlineQueue::class);
        $router->aliasMiddleware(KnownUserQueue::SIGNATURE, KnownUserQueue::class);

        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/queue-it.php', 'queue-it');
    }
}
