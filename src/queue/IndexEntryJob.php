<?php

declare(strict_types=1);

namespace boldminded\dexter\queue;

use boldminded\dexter\services\Config;
use boldminded\dexter\services\EntryPipelines;
use boldminded\dexter\services\IndexableEntry;
use boldminded\dexter\services\IndexerFactory;
use boldminded\dexter\services\Suffix;
use Craft;
use craft\elements\Entry;
use craft\queue\BaseJob;
use BoldMinded\DexterCore\Service\Indexer\IndexEntryCommand;
use BoldMinded\DexterCore\Service\Indexer\IndexerResponse;
use BoldMinded\DexterCore\Service\Indexer\IndexProvider;
use yii\queue\RetryableJobInterface;

class IndexEntryJob extends BaseJob implements RetryableJobInterface
{
    public string $uid;
    public string $title = '';
    public array $payload = [];

    public function execute($queue): void
    {
        $entry = Entry::find()
            ->uid($this->uid)
            ->one();

        $entryType = $entry?->type?->handle;

        if (!$entryType) {
            return;
        }

        $config = new Config();
        $indices = $config->get('indices.entries');
        $indexName = $indices[$entryType] ?? null;

        if (!$indexName) {
            return;
        }

        $command = new IndexEntryCommand(
            indexName: $indexName . Suffix::get($entry),
            indexable: new IndexableEntry($entry),
            config: $config,
            pipelines: EntryPipelines::getPipelines($config),
            queueJobName: IndexEntryJob::class,
        );

        /** @var IndexProvider $indexer */
        $indexer = IndexerFactory::create();
        /** @var IndexerResponse $response */
        $indexer->single($command, false);
    }

    protected function defaultDescription(): string
    {
        return Craft::t('dexter',
            'Indexing entry: {title}', [
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
