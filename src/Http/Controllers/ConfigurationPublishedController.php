<?php

namespace Jorbascrumps\QueueIt\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ConfigurationPublishedController
{
    /**
     * Stores Queue-it configuration when published from dashboard.
     * @throws ValidationException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $secretKey = config('queue-it.secret_key');

        $configHex = $request->post('integrationInfo');
        $configHash = $request->post('hash');

        if ($configHash !== hash_hmac('sha256', $configHex, $secretKey)) {
            throw ValidationException::withMessages([
                'hash' => 'Queue-it integration hash mismatch',
            ]);
        }

        $config = hex2bin($configHex);

        $configPath = config('queue-it.config_file');
        Storage::put($configPath, $config);

        return response()->json([
            'message' => 'Config saved',
        ]);
    }
}
