<?php

namespace boldminded\dexter\services\indexer;


use boldminded\dexter\events\UpdateConfigEvent;
use boldminded\dexter\queue\IndexFileJob;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\FilePipelines;
use boldminded\dexter\services\IndexableFile;
use boldminded\dexter\services\IndexerFactory;
use Craft;
use craft\elements\Asset;
use BoldMinded\DexterCore\Service\Indexer\IndexCommandCollection;
use BoldMinded\DexterCore\Service\Indexer\IndexFileCommand;
use BoldMinded\DexterCore\Service\Indexer\ReIndexCommands;
use yii\base\Event;

class ReIndexCommandsFileVolume implements ReIndexCommands
{
    private array $alerts = [];
    private string $indexName = '';

    public function __construct(
        private string $sourceId
    ) {
    }

    public function getCommandCollection(): IndexCommandCollection
    {
        $request = Craft::$app->getRequest();
        $offset  = $request->getBodyParam('offset');
        $limit   = $request->getBodyParam('limit');
        $clear   = $request->getBodyParam('clear');
        $siteId  = $request->getBodyParam('siteId') ?: Craft::$app->getSites()->getCurrentSite()->id;

        $query = Asset::find()
            ->volume($this->sourceId)
            ->siteId(['default', $siteId]);

        if ($offset !== null && $offset !== '') {
            $query->offset((int)$offset);
        }
        if ($limit !== null && $limit !== '') {
            $query->limit((int)$limit);
        }

        $files = $query->all();

        $config = new Config();

        Event::trigger(
            UpdateConfigEvent::class,
            UpdateConfigEvent::EVENT_DEXTER_UPDATE_CONFIG,
            new UpdateConfigEvent([
                'config' => $config,
                'siteId' => $siteId,
            ])
        );

        $indices = $config->get('indices.files');
        $indexer = IndexerFactory::create();
        $this->indexName = $indices[$this->sourceId] ?? '[unknown]';

        if (boolval($clear) === true) {
            $response = $indexer->clear($this->indexName);

            if ($response->isSuccess()) {
                $this->alerts[] = Craft::t('dexter',
                    '{indexName} has been cleared of all files.', [
                        'indexName' => $this->indexName
                    ]);
            } else {
                $this->alerts[] = Craft::t('dexter',
                    'Failed to clear {indexName} of all files: {errors}.', [
                        'indexName' => $this->indexName,
                        'errors' => implode(' ', $response->getErrors()),
                    ]);
            }
        }

        $commands = [];

        foreach ($files as $file) {
            $command = new IndexFileCommand(
                indexName: $this->indexName,
                indexable: new IndexableFile($file),
                config: $config,
                pipelines: FilePipelines::getPipelines($config),
                queueJobName: IndexFileJob::class
            );

            if (!empty($command->getValues())) {
                $commands[] = $command;
            }
        }

        return new IndexCommandCollection($commands);
    }


    public function getAlerts(): array
    {
        return $this->alerts;
    }

    public function getIndexName(): string
    {
        return $this->indexName;
    }
}
