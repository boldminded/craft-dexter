<?php

namespace boldminded\dexter\services\indexer;


use boldminded\dexter\queue\IndexEntryJob;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\EntryPipelines;
use boldminded\dexter\services\IndexableEntry;
use boldminded\dexter\services\IndexerFactory;
use Craft;
use craft\elements\Entry;
use Litzinger\DexterCore\Service\Indexer\IndexCommandCollection;
use Litzinger\DexterCore\Service\Indexer\IndexEntryCommand;
use Litzinger\DexterCore\Service\Indexer\ReIndexCommands;

class ReIndexCommandsSection implements ReIndexCommands
{
    private array $alerts = [];
    private string $indexName = '';

    public function __construct(
        private string $sourceId
    ) {
    }

    public function getCommandCollection(): IndexCommandCollection
    {
        // Build the Entry query
        $query = Entry::find()
            ->type($this->sourceId)
            ->siteId(Craft::$app->getSites()->getCurrentSite()->id);

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

        $entries = $query->all();

        $config = new Config();
        $indices = $config->get('indices.entries');
        $indexer = IndexerFactory::create();
        $this->indexName = $indices[$this->sourceId] ?? '[unknown]';

        if (boolval($clear) === true) {
            $response = $indexer->clear($this->indexName);

            if ($response->isSuccess()) {
                $this->alerts[] = Craft::t('dexter',
                    '{indexName} has been cleared of all entries.', [
                        'indexName' => $this->indexName
                    ]);
            } else {
                $this->alerts[] = Craft::t('dexter',
                    'Failed to clear {indexName} of all entries: {errors}.', [
                        'indexName' => $this->indexName,
                        'errors' => implode(' ', $response->getErrors()),
                    ]);
            }
        }

        $commands = [];

        foreach ($entries as $entry) {
            $command = new IndexEntryCommand(
                indexName: $this->indexName,
                indexable: new IndexableEntry($entry),
                config: $config,
                pipelines: EntryPipelines::getPipelines($config),
                queueJobName: IndexEntryJob::class
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
