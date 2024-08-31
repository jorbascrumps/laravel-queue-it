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

## Setup
Add your Queue-it credentials to your environment file.
```dotenv
QUEUE_IT_CUSTOMER_ID=
QUEUE_IT_SECRET_KEY=
```
> [!TIP]
> Your secret can be found in the [Go Queue-it self-service platform](https://go.queue-it.net) under Account > Settings > Integration.

## Usage
### Known User Queue
> [!CAUTION]
> You should not apply `KnownUserQueue` to static or cached pages, or assets.

Apply the `KnownUserQueue` middleware to any route or route group that you want to be queueable.
```php
use Illuminate\Support\Facades\Route;

Route::view('/event', 'event')->middleware('queue-it.known-user-queue');
```
You can also reference the class name instead of the alias if you prefer:
```php
use Illuminate\Support\Facades\Route;
use Jorbascrumps\QueueIt\Http\Middleware\KnownUserQueue;

Route::view('/event', 'event')->middleware(KnownUserQueue::class);
```

### Inline Queue
> [!CAUTION]
> You should not apply `InlineQueue` to static or cached pages, or assets.
> 
Apply the `InlineQueue` middleware to any route or route group that you want to be queueable. You must specify an event and queue domain. Other [customization options]() are also available.
```php
use Illuminate\Support\Facades\Route;
use Jorbascrumps\QueueIt\Http\Middleware\InlineQueue;

Route::view('/event', 'event')->middleware([
    InlineQueue::eventId('event1')
        ->queueDomain('jorbacrumps.queue-it.net')
]);
```

## Customizing config file location
By default, your integration configuration file is expected to be found in your application's storage as `queue-it-config.json`. If you need to store it under a different name in storage, you can specify this in your environment file:
```dotenv
QUEUE_IT_CONFIG_FILE=queue-it-config.json
```
