<?php

namespace Jorbascrumps\QueueIt\Test\Http\Middleware;

use Jorbascrumps\QueueIt\Http\Middleware\InlineQueue;
use Jorbascrumps\QueueIt\Test\TestCase;
use QueueIT\KnownUserV3\SDK\ActionTypes;
use QueueIT\KnownUserV3\SDK\RequestValidationResult;

class InlineQueueTest extends TestCase
{
    protected function defineWebRoutes($router): void
    {
        $subject = InlineQueue::eventId('test')->queueDomain(self::QUEUE_URL);

        $router->middleware($subject)->get(self::PAGE_URL, fn () => 'Page content');
    }

    public function testPerformsQueueRedirect(): void
    {
        $userInQueueService = $this->mockQueueService();
        $userInQueueService->validateQueueRequestResult = new RequestValidationResult(
            ActionTypes::QueueAction, null, null, self::QUEUE_URL, null, null
        );

        $response = $this->get(self::PAGE_URL);

        $response->assertRedirect(self::QUEUE_URL);
    }

    public function testTest2(): void
    {
        $userInQueueService = $this->mockQueueService();
        $userInQueueService->validateQueueRequestResult = new RequestValidationResult(
            ActionTypes::QueueAction, null, null, null, null, null
        );

        $response = $this->get(self::PAGE_URL . '?queueittoken=token');

        $response->assertRedirect(self::PAGE_URL);
    }

    public function testIgnoresInvalidAction(): void
    {
        $userInQueueService = $this->mockQueueService();
        $userInQueueService->validateQueueRequestResult = new RequestValidationResult(
            ActionTypes::IgnoreAction, null, null, null, null, null
        );

        $response = $this->get(self::PAGE_URL . '?queueittoken=token');

        $response->assertOk();
    }

    /**
     * @dataProvider aliasProvider
     */
    public function testAlias($expected, $actual): void
    {
        $this->assertSame($expected, (string) $actual);
    }

    public static function aliasProvider(): array
    {
        return [
            'default params' => [
                'queue-it.inline-queue:,,,15,1,,',
                new InlineQueue,
            ],
            'custom params' => [
                'queue-it.inline-queue:eventId,queueDomain,cookieDomain,10,,culture,layoutName',
                new InlineQueue('eventId', 'queueDomain', 'cookieDomain', 10, false, 'culture', 'layoutName'),
            ],
        ];
    }
}
