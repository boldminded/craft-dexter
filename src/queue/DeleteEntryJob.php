<?php

declare(strict_types=1);

namespace boldminded\dexter\queue;

use boldminded\dexter\services\IndexerFactory;
use Craft;
use craft\queue\BaseJob;
use Litzinger\DexterCore\Service\Indexer\DeleteEntryCommand;
use Litzinger\DexterCore\Service\Indexer\IndexerResponse;
use Litzinger\DexterCore\Service\Indexer\IndexProvider;
use yii\queue\RetryableJobInterface;

class DeleteEntryJob extends BaseJob implements RetryableJobInterface
{
    public string $uid;
    public string $title = '';
    public array $payload = [];

    public function execute($queue): void
    {
        $indexName = $this->payload['indexName'] ?? '';

        if (!$indexName) {
            return;
        }

        $command = new DeleteEntryCommand(
            indexName: $indexName,
            id: $this->uid,
            title: $this->title,
            queueJobName: DeleteEntryJob::class,
        );

        /** @var IndexProvider $indexer */
        $indexer = IndexerFactory::create();
        /** @var IndexerResponse $response */
        $indexer->delete($command, false);
    }

    protected function defaultDescription(): string
    {
        return Craft::t('dexter',
            'Deleting entry: {title}', [
                'title' => $this->title,
            ]
        );
    }

    public function getTtr()
    {
        // TODO: Implement getTtr() method.
    }

    public function canRetry($attempt, $error)
    {
        // TODO: Implement canRetry() method.
    }
}
