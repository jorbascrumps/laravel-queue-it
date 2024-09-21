<?php

namespace Jorbascrumps\QueueIt\Test\Http\Middleware;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Jorbascrumps\QueueIt\Events\UserQueued;
use Jorbascrumps\QueueIt\Http\Middleware\KnownUserQueue;
use Jorbascrumps\QueueIt\Test\Fixture;
use Jorbascrumps\QueueIt\Test\TestCase;
use QueueIT\KnownUserV3\SDK\ActionTypes;
use QueueIT\KnownUserV3\SDK\RequestValidationResult;
use TypeError;

/**
 * @backupStaticAttributes enabled
 */
class KnownUserQueueTest extends TestCase
{
    protected function defineWebRoutes($router): void
    {
        $router->middleware(KnownUserQueue::class)->get(self::PAGE_URL, fn () => 'Page content');
    }

    public function testMissingConfig(): void
    {
        KnownUserQueue::resolveIntegrationConfigurationUsing(static function () {
            return null;
        });

        $this->expectException(TypeError::class);

        $response = $this->withoutExceptionHandling()->getJson(self::PAGE_URL);
    }

    public function testInvalidConfig(): void
    {
        KnownUserQueue::resolveIntegrationConfigurationUsing(static function () {
            return '';
        });

        $response = $this->get(self::PAGE_URL);

        $response->assertHeader('X-Queue-Error');
    }

    public function testUsesDefaultFileStorageConfig(): void
    {
        $config = Fixture::get('config.json');
        Storage::shouldReceive('get')->once()->andReturn($config);

        $userInQueueService = $this->mockQueueService();
        $userInQueueService->validateQueueRequestResult = new RequestValidationResult(
            ActionTypes::QueueAction, null, null, self::QUEUE_URL, null, null
        );

        $response = $this->get(self::PAGE_URL);

        $response->assertRedirect(self::QUEUE_URL);
    }

    public function testPerformsQueueRedirect(): void
    {
        KnownUserQueue::resolveIntegrationConfigurationUsing(static function () {
            return Fixture::get('config.json');
        });

        $userInQueueService = $this->mockQueueService();
        $userInQueueService->validateQueueRequestResult = new RequestValidationResult(
            ActionTypes::QueueAction, null, null, self::QUEUE_URL, null, null
        );

        $response = $this->get(self::PAGE_URL);

        $response->assertRedirect(self::QUEUE_URL);
    }

    public function testTest2(): void
    {
        KnownUserQueue::resolveIntegrationConfigurationUsing(static function () {
            return Fixture::get('config.json');
        });

        $userInQueueService = $this->mockQueueService();
        $userInQueueService->validateQueueRequestResult = new RequestValidationResult(
            ActionTypes::QueueAction, null, null, null, null, null
        );

        $response = $this->get(self::PAGE_URL . '?queueittoken=token');

        $response->assertRedirect(self::PAGE_URL);
    }

    public function testIgnoresInvalidAction(): void
    {
        KnownUserQueue::resolveIntegrationConfigurationUsing(static function () {
            return Fixture::get('config.json');
        });

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

        KnownUserQueue::resolveIntegrationConfigurationUsing(static function () {
            return Fixture::get('config.json');
        });

        $userInQueueService = $this->mockQueueService();
        $userInQueueService->validateQueueRequestResult = new RequestValidationResult(
            ActionTypes::QueueAction, null, null, self::QUEUE_URL, null, null
        );

        $response = $this->get(self::PAGE_URL);

        Event::assertDispatched(UserQueued::class);
    }

    public function testUserQueueEligibility(): void
    {
        KnownUserQueue::resolveUserQueueEligibilityUsing(function () {
            return false;
        });

        $userInQueueService = $this->mockQueueService();
        $userInQueueService->validateQueueRequestResult = new RequestValidationResult(
            ActionTypes::QueueAction, null, null, self::QUEUE_URL, null, null
        );

        $response = $this->get(self::PAGE_URL);

        $response->assertOk();
    }
}
