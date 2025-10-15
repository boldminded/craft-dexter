<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use Litzinger\DexterCore\Contracts\QueueInterface;
use yii\queue\Queue as CraftQueue;

class QueueFactory
{
    public static function create(CraftQueue $queue): QueueInterface
    {
        return new Queue($queue);
    }
}
