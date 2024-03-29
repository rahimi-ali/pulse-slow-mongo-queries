<?php

namespace RahimiAli\Pulse\SlowMongoQueries;

use Illuminate\Support\ServiceProvider;
use Livewire\LivewireManager;
use RahimiAli\Pulse\SlowMongoQueries\Livewire\SlowMongoQueries;

class SlowMongoQueriesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'slow-mongo-queries');

        $this->callAfterResolving('livewire', function (LivewireManager $livewire) {
            $livewire->component('slow-mongo-queries', SlowMongoQueries::class);
        });
    }
}       