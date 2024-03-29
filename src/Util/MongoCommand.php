<?php

namespace RahimiAli\Pulse\SlowMongoQueries\Util;

use LogicException;

class MongoCommand
{
    private MongoCommandState $state;

    private readonly int $durationMicros;

    public function __construct(
        private readonly string $commandName,
        private readonly object $command,
        private readonly string $requestId,
    ) {
        $this->state = MongoCommandState::Started;
    }

    public function getCommandName(): string
    {
        return $this->commandName;
    }

    public function getCommand(): object
    {
        return $this->command;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function getState(): MongoCommandState
    {
        return $this->state;
    }

    public function succeed(int $durationMicros): self
    {
        if ($this->state !== MongoCommandState::Started) {
            throw new LogicException('Command must be in started state to succeed.');
        }

        $this->state = MongoCommandState::Succeeded;
        $this->durationMicros = $durationMicros;
        return $this;
    }

    public function fail(int $durationMicros): self
    {
        if ($this->state !== MongoCommandState::Started) {
            throw new LogicException('Command must be in started state to fail.');
        }

        $this->state = MongoCommandState::Failed;
        $this->durationMicros = $durationMicros;
        return $this;
    }

    public function getDurationMicros(): int
    {
        if (!isset($this->durationMicros)) {
            return 0;
        }

        return $this->durationMicros;
    }
}