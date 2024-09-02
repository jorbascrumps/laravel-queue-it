<?php

use Illuminate\Support\Facades\Route;
use Jorbascrumps\QueueIt\Http\Controllers\ConfigurationPublishedController;

Route::put(config('queue-it.config_update_url'), ConfigurationPublishedController::class)
    ->name('queue-it.config.update');
