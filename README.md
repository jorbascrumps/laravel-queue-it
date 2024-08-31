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
> [!NOTE]
> Your secret can be found in the [Go Queue-it self-service platform](https://go.queue-it.net) under Account > Settings > Integration.

## Usage
### Known User Queue
TODO

### Inline Queue
TODO

## Customizing config file location
By default, your integration configuration file is expected to be found in your application's storage as `queue-it-config.json`. If you need to store it under a different name in storage, you can specify this in your environment file:
```dotenv
QUEUE_IT_CONFIG_FILE=queue-it-config.json
```
