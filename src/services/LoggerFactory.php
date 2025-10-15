<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use Litzinger\DexterCore\Contracts\LoggerInterface;
use yii\log\Logger as CraftLogger;

class LoggerFactory
{
    public static function create(CraftLogger $logger): LoggerInterface
    {
        return new Logger($logger);
    }
}
