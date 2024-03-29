<?php

namespace RahimiAli\Pulse\SlowMongoQueries\Util;

enum MongoCommandState
{
    case Started;
    case Failed;
    case Succeeded;
}