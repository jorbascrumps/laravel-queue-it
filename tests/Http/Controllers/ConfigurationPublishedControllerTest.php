<?php

namespace Jorbascrumps\QueueIt\Test\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Jorbascrumps\QueueIt\Test\Fixture;
use Jorbascrumps\QueueIt\Test\TestCase;

class ConfigurationPublishedControllerTest extends TestCase
{
    public function testWithValidHash(): void
    {
        $config = Fixture::get('config.json');

        $integrationInfo = bin2hex($config);
        $hash = hash_hmac('sha256', $integrationInfo, '1234');

        $response = $this->putJson(route('queue-it.config.update'), [
            'integrationInfo' => $integrationInfo,
            'hash' => $hash,
        ]);

        $response->assertOk();
    }

    public function testWithInvalidHash(): void
    {
        $config = Fixture::get('config.json');

        $integrationInfo = bin2hex($config);
        $hash = hash_hmac('sha256', $integrationInfo, 'invalid');

        $response = $this->putJson(route('queue-it.config.update'), [
            'integrationInfo' => $integrationInfo,
            'hash' => $hash,
        ]);

        $response->assertJsonValidationErrorFor('hash');
    }

    public function testStoresConfig(): void
    {
        $config = Fixture::get('config.json');
        $configPath = config('queue-it.config_file');

        $integrationInfo = bin2hex($config);
        $hash = hash_hmac('sha256', $integrationInfo, '1234');

        Storage::shouldReceive('put')
            ->once()
            ->with($configPath, $config);

        $response = $this->putJson(route('queue-it.config.update'), [
            'integrationInfo' => $integrationInfo,
            'hash' => $hash,
        ]);

        $response->assertOk();
    }
}
