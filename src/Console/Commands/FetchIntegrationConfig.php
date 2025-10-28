<?php

namespace Jorbascrumps\QueueIt\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

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

    public const CONFIG_ENDPOINT = 'https://%s.queue-it.net/status/integrationconfig/secure/%s';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $config = config('queue-it');
        $url = sprintf(self::CONFIG_ENDPOINT, $config['customer_id'], $config['customer_id']);

        try {
            Http::sink(storage_path($config['config_file']))
                ->withHeaders([
                    'api-key' => $config['api_key'],
                ])
                ->get($url)
                ->throw();
        } catch (Throwable $e) {
            $this->error('Failed to fetch config: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info('Config fetched successfully');

        return self::SUCCESS;
    }
}
