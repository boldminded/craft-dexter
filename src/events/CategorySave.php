<?php

declare(strict_types=1);

namespace boldminded\dexter\events;

use boldminded\dexter\queue\IndexCategoryJob;
use boldminded\dexter\services\CategoryPipelines;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\IndexableCategory;
use boldminded\dexter\services\IndexerFactory;
use boldminded\dexter\services\Suffix;
use Craft;
use craft\base\Element;
use craft\elements\Category;
use craft\events\ModelEvent;
use craft\helpers\ElementHelper;
use BoldMinded\DexterCore\Service\Indexer\IndexCategoryCommand;
use BoldMinded\DexterCore\Service\Indexer\IndexerResponse;
use BoldMinded\DexterCore\Service\Indexer\IndexProvider;
use yii\base\Event;

class CategorySave
{
    public function subscribe()
    {
        Event::on(
            Category::class,
            Element::EVENT_AFTER_SAVE,
            function(ModelEvent $event) {
                try {
                    /** @var Category $category */
                    $category = $event->sender;

                    if (
                        $event->sender->resaving ||
                        ElementHelper::isDraft($category) ||
                        ElementHelper::isRevision($category)
                    ) {
                        return;
                    }

                    $this->handleSaveEvent($category);
                } catch (\Throwable $e) {
                    $message = Craft::t('dexter',
                        'Error indexing category: {msg}', [
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

    public function handleSaveEvent(Category $category)
    {
        $groupHandle = $category->group?->handle;

        if (!$groupHandle) {
            return;
        }

        $config = new Config();
        $indices = $config->get('indices.categories');
        $indexName = $indices[$groupHandle] ?? null;

        if (!$indexName) {
            return;
        }

        $command = new IndexCategoryCommand(
            indexName: $indexName . Suffix::get($category),
            indexable: new IndexableCategory($category),
            config: $config,
            pipelines: CategoryPipelines::getPipelines($config),
            queueJobName: IndexCategoryJob::class,
        );

        /** @var IndexProvider $indexer */
        $indexer = IndexerFactory::create();
        /** @var IndexerResponse $response */
        $response = $indexer->single($command, $config->get('useQueue'));
    }
}


