# Laravel Queue-it
Queue-it KnownUser v3 wrapper for Laravel.

```php
use Illuminate\Support\Facades\Route;

Route::view('/event', 'event')->middleware('queue-it.known-user-queue');
```

## Installation
```
composer require jorbascrumps/laravel-queue-it
```
