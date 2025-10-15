<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use Litzinger\DexterCore\Contracts\QueueInterface;
use \yii\queue\Queue as CraftQueue;

class Queue implements QueueInterface
{
    public function __construct(private CraftQueue $queue)
    {
    }

    public function push(string $job, array $data): void
    {
        $this->queue
            ->ttr(300)
            ->delay(1)
            ->priority(1024)
            ->push(new $job([
                'uid' => $data['uid'] ?? null,
                'title' => $data['title'] ?? null,
                'payload' => $data['payload'] ?? []
            ]))
        ;
    }
}
