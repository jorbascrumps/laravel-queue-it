<?php

namespace Jorbascrumps\QueueIt\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FetchIntegrationConfig extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'queue-it:fetch-config';

    /**
     * The console command description.
     */
    protected $description = 'Fetch Queue-it integration configuration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $config = config('queue-it');
        $url = sprintf('https://%s.queue-it.net/status/integrationconfig/secure/%s', $config['customer_id'], $config['customer_id']);

        try {
            $response = Http::withHeaders([
                'api-key' => $config['api_key'],
            ])
                ->get($url)
                ->throw();
        } catch (RequestException $e) {
            $this->error('Failed to fetch config: ' . $e->getMessage());

            return Command::FAILURE;
        }

        Storage::put($config['config_file'], $response->body());

        $this->info('Config fetched successfully');

        return Command::SUCCESS;
    }
}
