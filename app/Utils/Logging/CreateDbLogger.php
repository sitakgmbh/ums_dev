<?php

namespace App\Utils\Logging;

use Monolog\Logger;
use App\Utils\Logging\DbLogHandler;

class CreateDbLogger
{
    public function __invoke(array $config)
    {
        $logger = new Logger('db');
        $logger->pushHandler(new DbLogHandler());
        return $logger;
    }
}
