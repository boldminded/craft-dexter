<?php

namespace boldminded\dexter\services\indexer;


use boldminded\dexter\queue\IndexFileJob;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\FilePipelines;
use boldminded\dexter\services\IndexableFile;
use boldminded\dexter\services\IndexerFactory;
use boldminded\dexter\services\Suffix;
use Craft;
use craft\elements\Asset;
use BoldMinded\DexterCore\Service\Indexer\IndexCommandCollection;
use BoldMinded\DexterCore\Service\Indexer\IndexFileCommand;
use BoldMinded\DexterCore\Service\Indexer\ReIndexCommands;

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
        $query = Asset::find()
            ->volume($this->sourceId)
            ->siteId(['default', Craft::$app->getSites()->getCurrentSite()->id]);

        $request = Craft::$app->getRequest();
        $offset  = $request->getBodyParam('offset');
        $limit   = $request->getBodyParam('limit');
        $clear   = $request->getBodyParam('clear');

        if ($offset !== null && $offset !== '') {
            $query->offset((int)$offset);
        }
        if ($limit !== null && $limit !== '') {
            $query->limit((int)$limit);
        }

        $files = $query->all();

        $config = new Config();
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
                indexName: $this->indexName . Suffix::get($file),
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
