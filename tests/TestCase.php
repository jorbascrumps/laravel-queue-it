<?php

namespace Jorbascrumps\QueueIt\Test;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;
use Jorbascrumps\QueueIt\ServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use QueueIT\KnownUserV3\SDK\KnownUser;
use ReflectionProperty;

abstract class TestCase extends Orchestra
{
    use WithWorkbench;

    public const PAGE_URL = '/queueable';

    public const QUEUE_URL = 'https://queue-it.net';

    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function mockConfig(bool $returnNull = false, bool $throw = false): void
    {
        $config = Fixture::get('config.json');

        $mock = Storage::shouldReceive('get')->once();

        if ($returnNull) {
            $mock->andReturnNull();
        } else {
            $mock->andReturn($config);
        }

        if ($throw) {
            $mock->andThrow(FileNotFoundException::class);
        }
    }

    protected function mockQueueService(): UserInQueueServiceMock
    {
        $userInQueueService = new UserInQueueServiceMock;

        $r = new ReflectionProperty(KnownUser::class, 'userInQueueService');
        $r->setAccessible(true);
        $r->setValue(null, $userInQueueService);

        return $userInQueueService;
    }
}
