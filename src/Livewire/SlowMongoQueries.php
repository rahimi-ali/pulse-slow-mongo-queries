<?php

namespace RahimiAli\Pulse\SlowMongoQueries\Livewire;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Laravel\Pulse\Livewire\Card;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Url;
use RahimiAli\Pulse\SlowMongoQueries\Recorder\SlowMongoQueriesRecorder;

#[Lazy]
class SlowMongoQueries extends Card
{
    /** @var 'slowest'|'count' */
    #[Url(as: 'slow-mongo-queries-order')]
    public string $orderBy = 'slowest';

    public function render(): Renderable
    {
        [$slowQueries, $time, $runAt] = $this->remember(
            fn() => $this->aggregate(
                'slow_mongo_query',
                ['max', 'count'],
                match ($this->orderBy) {
                    'count' => 'count',
                    default => 'max',
                },
            )->map(function ($row) {
                [$command, $location] = json_decode($row->key, flags: JSON_THROW_ON_ERROR);

                return (object)[
                    'command' => $command,
                    'location' => $location,
                    'slowest' => $row->max,
                    'count' => $row->count,
                ];
            }),
            $this->orderBy,
        );

        return View::make('slow-mongo-queries::livewire.slow-mongo-queries', [
            'time' => $time,
            'runAt' => $runAt,
            'config' => [
                ...Config::get('pulse.recorders.' . SlowMongoQueriesRecorder::class),
            ],
            'slowQueries' => $slowQueries,
        ]);
    }
}