<?php

namespace Jorbascrumps\QueueIt\Test;

use Illuminate\Support\Facades\Storage;

class Fixture
{
    public static function get($path): ?string
    {
        return Storage::build([
            'driver' => 'local',
            'root' => implode(DIRECTORY_SEPARATOR, [
                __DIR__, 'fixtures'
            ]),
        ])->get($path);
    }
}
