<?php

namespace RahimiAli\Pulse\SlowMongoQueries\Util;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * @method static void dispatch(MongoCommand $command)
 */
class MongoQuerySucceeded
{
    use Dispatchable;

    public function __construct(
        public readonly MongoCommand $command,
    ) {
    }
}