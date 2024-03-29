<?php

namespace RahimiAli\Pulse\SlowMongoQueries\Util;

use MongoDB\Driver\Monitoring\CommandFailedEvent;
use MongoDB\Driver\Monitoring\CommandStartedEvent;
use MongoDB\Driver\Monitoring\CommandSubscriber;
use MongoDB\Driver\Monitoring\CommandSucceededEvent;

class MongoSubscriber implements CommandSubscriber
{
    /** @var MongoCommand[] */
    private array $startedCommands = [];

    public function commandStarted(CommandStartedEvent $event): void
    {
        $this->startedCommands[] = new MongoCommand(
            $event->getCommandName(),
            $event->getCommand(),
            $event->getRequestId(),
        );
    }

    public function commandSucceeded(CommandSucceededEvent $event): void
    {
        $requestId = $event->getRequestId();

        foreach ($this->startedCommands as $key => $command) {
            if ($command->getRequestId() === $requestId) {
                $command->succeed($event->getDurationMicros());

                MongoQuerySucceeded::dispatch($command);

                // cleanup memory
                unset($this->startedCommands[$key]);

                break;
            }
        }
    }

    public function commandFailed(CommandFailedEvent $event): void
    {
        $requestId = $event->getRequestId();

        foreach ($this->startedCommands as $key => $command) {
            if ($command->getRequestId() === $requestId) {
                $command->fail($event->getDurationMicros());

                // cleanup memory
                unset($this->startedCommands[$key]);

                break;
            }
        }
    }
}