<?php

declare(strict_types=1);

namespace boldminded\dexter\queue;

use boldminded\dexter\services\Config;
use boldminded\dexter\services\FilePipelines;
use boldminded\dexter\services\IndexableFile;
use boldminded\dexter\services\IndexerFactory;
use Craft;
use craft\elements\Asset;
use craft\queue\BaseJob;
use Litzinger\DexterCore\Service\Indexer\IndexerResponse;
use Litzinger\DexterCore\Service\Indexer\IndexFileCommand;
use Litzinger\DexterCore\Service\Indexer\IndexProvider;
use yii\queue\RetryableJobInterface;

class IndexFileJob extends BaseJob implements RetryableJobInterface
{
    public string $uid;
    public string $title = '';
    public array $payload = [];

    public function execute($queue): void
    {
        $file = Asset::find()
            ->uid($this->uid)
            ->one();

        $volumeHandle = $file?->volume?->fsHandle;

        if (!$volumeHandle) {
            return;
        }

        $config = new Config();
        $indices = $config->get('indices.files');
        $indexName = $indices[$volumeHandle] ?? null;

        if (!$indexName) {
            return;
        }

        $command = new IndexFileCommand(
            indexName: $indexName,
            indexable: new IndexableFile($file),
            config: $config,
            pipelines: FilePipelines::getPipelines($config),
            queueJobName: IndexFileJob::class,
        );

        /** @var IndexProvider $indexer */
        $indexer = IndexerFactory::create();
        /** @var IndexerResponse $response */
        $indexer->single($command, false);
    }

    protected function defaultDescription(): string
    {
        return Craft::t('dexter',
            'Indexing file: {title}', [
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
