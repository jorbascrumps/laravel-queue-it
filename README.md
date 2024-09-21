# Laravel Queue-it
![Tests](https://github.com/jorbascrumps/laravel-queue-it/actions/workflows/run-tests.yml/badge.svg)

Queue-it integration for Laravel.

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
> Your secret can be found in the [Go Queue-it self-service platform](https://go.queue-it.net) under _Account > Settings > Integration_.

## Usage
### Known User Queue
> [!CAUTION]
> You should not apply `KnownUserQueue` to static or cached pages, or assets.

Known User Queue allows control over queues via the Go Queue-it self-service platform and requires an integration config. See [Exporting integration config](#exporting-integration-config) for details on how you can acquire it. Once you've added an integration config to your project you can apply the `KnownUserQueue` middleware to any route or route group that you want to be queueable.
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

## User queue eligibility
You may not want to send every user through the queue. In these scenarios you can provide a customer resolver to determine queue eligibility within a service provider.
```php
KnownUserQueue::resolveUserQueueEligibilityUsing(function (Authenticatable $user) {
    return ! $user->isAdmin();
});
```
The callback will be resolved via the container so you can inject the authenticated user or any other depedency you may need.

## Exporting integration config
Known User Queues require an integration configuration file that contains logic for how and when queues should be managed. There are several options available to add this file to your project.
### Publish webhook
This package includes a webhook route that you can register in your account to push your integration configuration file to anytime you make changes. You can configure this setting under _Integrations > Overview > Settings > Publish web endpoint_.
> [!TIP]
> You can customize the webhook route in your environment file:
> ```
> QUEUE_IT_CONFIG_UPDATE_URL=/webhooks/queue-it/config-published
> ```
### Using Artisan
This package includes an Artisan command to fetch and store your integration file anytime you need to. Note that this method requires an API key.
```
php artisan queue-it:fetch-config
```
> [!IMPORTANT]
> You can specify your API key in your environment file:
> ```
> QUEUE_IT_API_KEY=your-api-key
> ```
### Manual download
Download options can be found in your account under _Integrations > Overview > Latest KnownUser configuration_. You should save this to your application's storage directory.

### Customizing config file location
By default, your integration configuration file is expected to be found in your application's storage as `queue-it-config.json`. If you need to store it under a different name in storage, you can specify this in your environment file:
```dotenv
QUEUE_IT_CONFIG_FILE=queue-it-config.json
```
