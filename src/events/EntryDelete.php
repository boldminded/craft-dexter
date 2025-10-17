<?php

declare(strict_types=1);

namespace boldminded\dexter\events;

use boldminded\dexter\queue\DeleteEntryJob;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\IndexerFactory;
use Craft;
use craft\base\Element;
use craft\elements\Entry;
use BoldMinded\DexterCore\Service\Indexer\DeleteEntryCommand;
use BoldMinded\DexterCore\Service\Indexer\IndexerResponse;
use BoldMinded\DexterCore\Service\Indexer\IndexProvider;
use yii\base\Event;

class EntryDelete
{
    public function subscribe()
    {
        Event::on(
            Entry::class,
            Element::EVENT_AFTER_DELETE,
            function(Event $event) {
                try {
                    /** @var Entry $entry */
                    $entry = $event->sender;

                    $this->handleDeleteEvent($entry);
                } catch (\Throwable $e) {
                    $message = Craft::t('dexter',
                        'Error deleting entry: {msg}', [
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

    public function handleDeleteEvent(Entry $entry)
    {
        $typeHandle = $entry->type?->handle;

        if (!$typeHandle) {
            return;
        }

        $config = new Config();

        Event::trigger(
            UpdateConfigEvent::class,
            UpdateConfigEvent::EVENT_DEXTER_UPDATE_CONFIG,
            new UpdateConfigEvent([
                'config' => $config,
                'element' => $entry,
            ])
        );

        $indices = $config->get('indices.entries');
        $indexName = $indices[$typeHandle] ?? null;

        if (!$indexName) {
            return;
        }

        $command = new DeleteEntryCommand(
            indexName: $indexName,
            id: $entry->uid,
            title: $entry->title ?? '',
            queueJobName: DeleteEntryJob::class,
        );

        /** @var IndexProvider $indexer */
        $indexer = IndexerFactory::create();
        /** @var IndexerResponse $response */
        $response = $indexer->delete($command, $config->get('useQueue'));
    }
}


