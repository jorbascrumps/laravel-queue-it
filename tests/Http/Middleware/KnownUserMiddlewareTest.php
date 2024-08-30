<?php

namespace Jorbascrumps\QueueIt\Test\Http\Middleware;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Jorbascrumps\QueueIt\Http\Middleware\KnownUserMiddleware;
use Jorbascrumps\QueueIt\Test\Fixture;
use Jorbascrumps\QueueIt\Test\HttpRequestProviderMock;
use Jorbascrumps\QueueIt\Test\TestCase;
use Jorbascrumps\QueueIt\Test\UserInQueueServiceMock;
use QueueIT\KnownUserV3\SDK\ActionTypes;
use QueueIT\KnownUserV3\SDK\KnownUser;
use QueueIT\KnownUserV3\SDK\KnownUserException;
use QueueIT\KnownUserV3\SDK\RequestValidationResult;
use ReflectionProperty;

class KnownUserMiddlewareTest extends TestCase
{
    public const PAGE_URL = '/queueable';

    public const QUEUE_URL = 'https://queue-it.net';

    protected function defineWebRoutes($router): void
    {
        $router->middleware(KnownUserMiddleware::class)->get(self::PAGE_URL, fn () => 'Page content');
    }

    public function testFeatureDisabled(): void
    {
        Config::set('queue-it.enabled', false);

        $response = $this->get(self::PAGE_URL);

        $response->assertOk();
    }

    public function testMissingConfig(): void
    {
        $this->mockConfig(false, true);

        $this->expectException(FileNotFoundException::class);

        $this->withoutExceptionHandling()->getJson(self::PAGE_URL);
    }

    public function testInvalidConfig(): void
    {
        $this->mockConfig(true);

        $this->expectException(KnownUserException::class);

        $this->withoutExceptionHandling()->getJson(self::PAGE_URL);
    }

    public function testPerformsQueueRedirect(): void
    {
        $this->mockConfig();

        $this->mockRequestProvider();

        $userInQueueService = $this->mockQueueService();
        $userInQueueService->validateQueueRequestResult = new RequestValidationResult(
            ActionTypes::QueueAction, null, null, self::QUEUE_URL, null, null
        );

        $response = $this->get(self::PAGE_URL);

        $response->assertRedirect(self::QUEUE_URL);
    }

    public function testTest2(): void
    {
        $this->mockConfig();

        $this->mockRequestProvider();

        $userInQueueService = $this->mockQueueService();
        $userInQueueService->validateQueueRequestResult = new RequestValidationResult(
            ActionTypes::QueueAction, null, null, null, null, null
        );

        $response = $this->get(self::PAGE_URL . '?queueittoken=token');

        $response->assertRedirect(self::PAGE_URL);
    }

    public function testIgnoresInvalidAction(): void
    {
        $this->mockConfig();

        $this->mockRequestProvider();

        $userInQueueService = $this->mockQueueService();
        $userInQueueService->validateQueueRequestResult = new RequestValidationResult(
            ActionTypes::IgnoreAction, null, null, null, null, null
        );

        $response = $this->get(self::PAGE_URL . '?queueittoken=token');

        $response->assertOk();
    }

    private function mockRequestProvider(): HttpRequestProviderMock
    {
        $httpRequestProvider = new HttpRequestProviderMock;

        $r = new ReflectionProperty(KnownUser::class, 'httpRequestProvider');
        $r->setAccessible(true);
        $r->setValue(null, $httpRequestProvider);

        return $httpRequestProvider;
    }

    private function mockQueueService(): UserInQueueServiceMock
    {
        $userInQueueService = new UserInQueueServiceMock;

        $r = new ReflectionProperty(KnownUser::class, 'userInQueueService');
        $r->setAccessible(true);
        $r->setValue(null, $userInQueueService);

        return $userInQueueService;
    }

    private function mockConfig(bool $returnNull = false, bool $throw = false): void
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
}
