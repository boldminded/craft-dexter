<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use Litzinger\DexterCore\Contracts\LoggerInterface;
use yii\log\Logger as CraftLogger;

class Logger implements LoggerInterface
{
    public function __construct(private CraftLogger $logger)
    {
    }

    public function emergency(string $message): void
    {
        $this->logger->log($message, CraftLogger::LEVEL_ERROR);
    }

    public function alert(string $message): void
    {
        $this->logger->log($message, CraftLogger::LEVEL_TRACE);
    }

    public function critical(string $message): void
    {
        $this->logger->log($message, CraftLogger::LEVEL_ERROR);
    }

    public function error(string $message): void
    {
        $this->logger->log($message, CraftLogger::LEVEL_ERROR);
    }

    public function warning(string $message): void
    {
        $this->logger->log($message, CraftLogger::LEVEL_WARNING);
    }

    public function notice(string $message): void
    {
        $this->logger->log($message, CraftLogger::LEVEL_TRACE);
    }

    public function info(string $message): void
    {
        $this->logger->log($message, CraftLogger::LEVEL_INFO);
    }

    public function debug(string $message): void
    {
        $this->logger->log($message, CraftLogger::LEVEL_TRACE);
    }
}
