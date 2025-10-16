<?php

declare(strict_types=1);

namespace boldminded\dexter\events;

use boldminded\dexter\queue\IndexEntryJob;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\EntryPipelines;
use boldminded\dexter\services\IndexableEntry;
use boldminded\dexter\services\IndexerFactory;
use boldminded\dexter\services\Suffix;
use Craft;
use craft\base\Element;
use craft\elements\Entry;
use craft\events\ModelEvent;
use craft\helpers\ElementHelper;
use BoldMinded\DexterCore\Service\Indexer\IndexEntryCommand;
use BoldMinded\DexterCore\Service\Indexer\IndexerResponse;
use BoldMinded\DexterCore\Service\Indexer\IndexProvider;
use yii\base\Event;

class EntrySave
{
    public function subscribe()
    {
         Event::on(
             Entry::class,
             Element::EVENT_AFTER_SAVE,
             function(ModelEvent $event) {
                 try {
                     /** @var Entry $entry */
                     $entry = $event->sender;

                     if (
                         $event->sender->resaving ||
                         ElementHelper::isDraft($entry) ||
                         ElementHelper::isRevision($entry)
                     ) {
                         return;
                     }

                     $this->handleSaveEvent($entry);
                 } catch (\Throwable $e) {
                     $message = Craft::t('dexter',
                         'Error indexing entry: {msg}', [
                             'msg' => $e->getMessage(),
                         ]
                     );

                     Craft::error($message, __METHOD__);

                     Craft::$app
                         ->getSession()
                         ->setFlash(
                             'dexterError',
                             $message
                         );
                 }
             }
         );
    }

    public function handleSaveEvent(Entry $entry)
    {
        $typeHandle = $entry->type?->handle;

        if (!$typeHandle) {
            return;
        }

        $config = new Config();
        $indices = $config->get('indices.entries');
        $indexName = $indices[$typeHandle] ?? null;

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
        $response = $indexer->single($command, $config->get('useQueue'));
    }
}


