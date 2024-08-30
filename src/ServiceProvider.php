<?php

namespace Jorbascrumps\QueueIt;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/queue-it.php' => config_path('queue-it.php'),
        ]);

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
