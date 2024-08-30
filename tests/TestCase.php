<?php

namespace Jorbascrumps\QueueIt\Test;

use Jorbascrumps\QueueIt\QueueItServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use WithWorkbench;

    protected function getPackageProviders($app): array
    {
        return [
            QueueItServiceProvider::class,
        ];
    }
}
