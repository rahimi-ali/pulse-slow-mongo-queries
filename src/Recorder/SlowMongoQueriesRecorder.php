<?php

namespace RahimiAli\Pulse\SlowMongoQueries\Recorder;

use Illuminate\Config\Repository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Lottery;
use Illuminate\Support\Str;
use Laravel\Pulse\Facades\Pulse;
use RahimiAli\Pulse\SlowMongoQueries\Util\MongoQuerySucceeded;

class SlowMongoQueriesRecorder
{
    private const DEFAULT_THRESHOLD = 200_000; // 200ms

    /** @var class-string[] */
    public array $listen = [MongoQuerySucceeded::class];

    public function __construct(
        protected Repository $config,
    ) {
    }

    public function record(MongoQuerySucceeded $event): void
    {
        $timestamp = Carbon::now()->getTimestamp();
        $durationMicros = $event->command->getDurationMicros();
        $command = json_decode(json_encode($event->command->getCommand()), true)[$event->command->getCommandName()] . '.' . $event->command->getCommandName();
        $location = $this->resolveLocation();

        Pulse::lazy(function () use ($timestamp, $durationMicros, $command, $location) {
            if (
                $durationMicros < $this->config->get('pulse.recorders.' . self::class . '.threshold', self::DEFAULT_THRESHOLD) ||
                !$this->shouldSample()
            ) {
                return;
            }

            Pulse::record(
                type: 'slow_mongo_query',
                key: json_encode([$command, $location], flags: JSON_THROW_ON_ERROR),
                value: $durationMicros,
                timestamp: $timestamp - (int) ($durationMicros / 1_000_000),
            )->max()->count();
        });
    }

    private function resolveLocation(): string
    {
        $backtrace = collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))->skip(2);

        $frame = $backtrace->firstWhere(fn(array $frame) => isset($frame['file']) && !$this->isInternalFile($frame['file']));

        if ($frame === null) {
            return '';
        }

        return $this->formatLocation($frame['file'] ?? 'unknown', $frame['line'] ?? null);
    }

    private function isInternalFile(string $file): bool
    {
        return Str::startsWith($file, base_path('vendor'))
            || $file === base_path('artisan')
            || $file === public_path('index.php');
    }

    private function formatLocation(string $file, ?int $line): string
    {
        return Str::replaceFirst(base_path(DIRECTORY_SEPARATOR), '', $file) . (is_int($line) ? (':' . $line) : '');
    }

    private function shouldSample(): bool
    {
        return Lottery::odds(
            $this->config->get('pulse.recorders.' . self::class . '.sample_rate', 0)
        )->choose();
    }
}