<?php

namespace App\Logging;

use Monolog\Formatter\LogstashFormatter;

class LogstashLogger
{
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LogstashFormatter("sendy"));
        }
    }
}
