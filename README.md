# Laravel Pulse Slow MongoDB Queries

Slow queries card for Laravel Pulse just for MongoDB. This package does not assume you use any specific library,
the only requirement is that you should add a subscriber to the MongoDB client class used by your application to connect to mongo and run commands. 

## Installation

Install using Composer:
```bash
composer require rahimi-ali/pulse-slow-mongo-queries
```

## Usage

Add the MongoCommandSubscriber to your mongoDB client:
```php
$client = new Client('connection string', []);

$client->getManager()->addSubscriber(new \RahimiAli\Pulse\SlowMongoQueries\Util\MongoSubscriber());
```

Add the recorder to the `config/pulse.php` configuration file:
```php
'recorders' => [
    // ...
    
    \RahimiAli\Pulse\SlowMongoQueries\Recorder\SlowMongoQueriesRecorder::class => [
        'threshold' => 100_000, // in microseconds
        'sample_rate' => 1, // between 0 and 1
    ]
]
```

Add the card to your pulse dashboard:
```blade
<x-pulse>
    <livewire:pulse.servers cols="full" />

    <livewire:pulse.usage cols="4" rows="3" />

    <livewire:pulse.queues cols="4" />

    <livewire:pulse.cache cols="4" />

    <livewire:pulse.slow-queries cols="8" />

    <livewire:slow-mongo-queries cols="8" /> {{-- this is rendered under the slow queries card --}}

    <livewire:pulse.exceptions cols="6" />

    <livewire:pulse.slow-requests cols="6" />

    <livewire:pulse.slow-jobs cols="6" />

    <livewire:pulse.slow-outgoing-requests cols="6" />
</x-pulse>
```
