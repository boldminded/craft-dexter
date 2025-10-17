<?php

declare(strict_types=1);

namespace boldminded\dexter\queue;

use boldminded\dexter\events\UpdateConfigEvent;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\FilePipelines;
use boldminded\dexter\services\IndexableFile;
use boldminded\dexter\services\IndexerFactory;
use Craft;
use craft\elements\Asset;
use craft\queue\BaseJob;
use BoldMinded\DexterCore\Service\Indexer\IndexerResponse;
use BoldMinded\DexterCore\Service\Indexer\IndexFileCommand;
use BoldMinded\DexterCore\Service\Indexer\IndexProvider;
use yii\base\Event;
use yii\queue\RetryableJobInterface;

class IndexFileJob extends BaseJob implements RetryableJobInterface
{
    public string $uid;
    public string $title = '';
    public array $payload = [];

    public function execute($queue): void
    {
        $siteId = $this->payload['siteId'] ?? null;

        $file = Asset::find()
            ->uid($this->uid)
            ->siteId($siteId)
            ->one();

        $volumeHandle = $file?->volume?->fsHandle;

        if (!$volumeHandle) {
            return;
        }

        $config = new Config();

        Event::trigger(
            UpdateConfigEvent::class,
            UpdateConfigEvent::EVENT_DEXTER_UPDATE_CONFIG,
            new UpdateConfigEvent([
                'config' => $config,
                'element' => $file,
            ])
        );

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
