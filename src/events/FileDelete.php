<?php

declare(strict_types=1);

namespace boldminded\dexter\events;

use boldminded\dexter\queue\DeleteFileJob;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\IndexerFactory;
use Craft;
use craft\base\Element;
use craft\elements\Asset as AssetElement;
use BoldMinded\DexterCore\Service\Indexer\DeleteFileCommand;
use BoldMinded\DexterCore\Service\Indexer\IndexerResponse;
use BoldMinded\DexterCore\Service\Indexer\IndexProvider;
use yii\base\Event;

class FileDelete
{
    private static array $saved = [];

    public function subscribe()
    {
        Event::on(
            AssetElement::class,
            Element::EVENT_AFTER_DELETE,
            function(Event $event) {
                try {
                    /** @var AssetElement $file */
                    $file = $event->sender;

                    $this->handleDeleteEvent($file);
                } catch (\Throwable $e) {
                    $message = Craft::t('dexter',
                        'Error deleting file: {msg}', [
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

    public function handleDeleteEvent(AssetElement $file)
    {
        $volumeHandle = $file->volume?->fsHandle;

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

        $command = new DeleteFileCommand(
            indexName: $indexName,
            id: $file->uid,
            title: $file->title ?? '',
            queueJobName: DeleteFileJob::class,
        );

        /** @var IndexProvider $indexer */
        $indexer = IndexerFactory::create();
        /** @var IndexerResponse $response */
        $response = $indexer->delete($command, $config->get('useQueue'));
    }
}


