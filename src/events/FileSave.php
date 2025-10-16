<?php

declare(strict_types=1);

namespace boldminded\dexter\events;

use boldminded\dexter\queue\IndexFileJob;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\FilePipelines;
use boldminded\dexter\services\IndexableFile;
use boldminded\dexter\services\IndexerFactory;
use boldminded\dexter\services\Suffix;
use Craft;
use craft\base\Element;
use craft\elements\Asset as AssetElement;
use craft\events\ModelEvent;
use BoldMinded\DexterCore\Service\Indexer\IndexerResponse;
use BoldMinded\DexterCore\Service\Indexer\IndexFileCommand;
use BoldMinded\DexterCore\Service\Indexer\IndexProvider;
use yii\base\Event;

class FileSave
{
    private static array $saved = [];

    public function subscribe()
    {
        Event::on(
            AssetElement::class,
            Element::EVENT_AFTER_SAVE,
            function(ModelEvent $event) {
                try {
                    /** @var AssetElement $file */
                    $file = $event->sender;

                    if (in_array($file->uid, self::$saved)) {
                        return;
                    }

                    self::$saved[] = $file->uid;

                    $this->handleSaveEvent($file);
                } catch (\Throwable $e) {
                    $message = Craft::t('dexter',
                        'Error indexing asset: {msg}', [
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

    public function handleSaveEvent(AssetElement $file)
    {
        $volumeHandle = $file->volume?->fsHandle;

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
            indexName: $indexName . Suffix::get($file),
            indexable: new IndexableFile($file),
            config: $config,
            pipelines: FilePipelines::getPipelines($config),
            queueJobName: IndexFileJob::class,
        );

        /** @var IndexProvider $indexer */
        $indexer = IndexerFactory::create();
        /** @var IndexerResponse $response */
        $response = $indexer->single($command, $config->get('useQueue'));
    }
}


