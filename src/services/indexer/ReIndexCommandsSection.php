<?php

namespace boldminded\dexter\services\indexer;


use boldminded\dexter\events\UpdateConfigEvent;
use boldminded\dexter\queue\IndexEntryJob;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\EntryPipelines;
use boldminded\dexter\services\IndexableEntry;
use boldminded\dexter\services\IndexerFactory;
use Craft;
use craft\elements\Entry;
use BoldMinded\DexterCore\Service\Indexer\IndexCommandCollection;
use BoldMinded\DexterCore\Service\Indexer\IndexEntryCommand;
use BoldMinded\DexterCore\Service\Indexer\ReIndexCommands;
use yii\base\Event;

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
        $request = Craft::$app->getRequest();
        $offset  = $request->getBodyParam('offset');
        $limit   = $request->getBodyParam('limit');
        $clear   = $request->getBodyParam('clear');
        $siteId  = $request->getBodyParam('siteId') ?: Craft::$app->getSites()->getCurrentSite()->id;

        // Build the Entry query
        $query = Entry::find()
            ->type($this->sourceId)
            ->siteId($siteId);

        if ($offset !== null && $offset !== '') {
            $query->offset((int)$offset);
        }
        if ($limit !== null && $limit !== '') {
            $query->limit((int)$limit);
        }

        $entries = $query->all();

        $config = new Config();

        Event::trigger(
            UpdateConfigEvent::class,
            UpdateConfigEvent::EVENT_DEXTER_UPDATE_CONFIG,
            new UpdateConfigEvent([
                'config' => $config,
                'siteId' => $siteId,
            ])
        );

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
