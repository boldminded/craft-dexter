<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use BoldMinded\DexterCore\Contracts\QueueInterface;
use \yii\queue\Queue as CraftQueue;

class Queue implements QueueInterface
{
    public function __construct(private CraftQueue $queue)
    {
    }

    public function push(string $job, array $data): void
    {
        $payload = $data['payload'] ?? [];

        // We're not passing the full object to the queue, just the necessary data to find it again when the job is
        // executed, but we also need the siteId to find the correct instance of an item.
        if (!isset($payload['siteId'])) {
            $payload['siteId'] = $data['siteId'] ?? null;
        }

        $this->queue
            ->ttr(300)
            ->delay(1)
            ->priority(1024)
            ->push(new $job([
                'uid' => $data['uid'] ?? null,
                'title' => $data['title'] ?? null,
                'payload' => $payload
            ]))
        ;
    }
}
