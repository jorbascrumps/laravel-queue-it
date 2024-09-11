<?php

namespace Jorbascrumps\QueueIt\Test\Http\Middleware;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Event;
use Jorbascrumps\QueueIt\Events\UserQueued;
use Jorbascrumps\QueueIt\Http\Middleware\KnownUserQueue;
use Jorbascrumps\QueueIt\Test\TestCase;
use QueueIT\KnownUserV3\SDK\ActionTypes;
use QueueIT\KnownUserV3\SDK\KnownUserException;
use QueueIT\KnownUserV3\SDK\RequestValidationResult;

class KnownUserQueueTest extends TestCase
{
    protected function defineWebRoutes($router): void
    {
        $router->middleware(KnownUserQueue::class)->get(self::PAGE_URL, fn () => 'Page content');
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

        $response = $this->get(self::PAGE_URL);

        $response->assertHeader('X-Queue-Error');
    }

    public function testPerformsQueueRedirect(): void
    {
        $this->mockConfig();

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

        $userInQueueService = $this->mockQueueService();
        $userInQueueService->validateQueueRequestResult = new RequestValidationResult(
            ActionTypes::IgnoreAction, null, null, null, null, null
        );

        $response = $this->get(self::PAGE_URL . '?queueittoken=token');

        $response->assertOk();
    }

    public function testEmitsOnQueueRedirect(): void
    {
        Event::fake();

        $this->mockConfig();

        $userInQueueService = $this->mockQueueService();
        $userInQueueService->validateQueueRequestResult = new RequestValidationResult(
            ActionTypes::QueueAction, null, null, self::QUEUE_URL, null, null
        );

        $response = $this->get(self::PAGE_URL);

        Event::assertDispatched(UserQueued::class);
    }
}
