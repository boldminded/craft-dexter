<?php

declare(strict_types=1);

namespace boldminded\dexter\events;

use boldminded\dexter\queue\DeleteCategoryJob;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\IndexerFactory;
use Craft;
use craft\base\Element;
use craft\elements\Category;
use craft\helpers\ElementHelper;
use BoldMinded\DexterCore\Service\Indexer\DeleteCategoryCommand;
use BoldMinded\DexterCore\Service\Indexer\IndexerResponse;
use BoldMinded\DexterCore\Service\Indexer\IndexProvider;
use yii\base\Event;

class CategoryDelete
{
    public function subscribe()
    {
        Event::on(
            Category::class,
            Element::EVENT_AFTER_DELETE,
            function(Event $event) {
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

                    $this->handleDeleteEvent($category);
                } catch (\Throwable $e) {
                    $message = Craft::t('dexter',
                        'Error deleting category: {msg}', [
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

    public function handleDeleteEvent(Category $category)
    {
        $groupHandle = $category->group?->handle;

        if (!$groupHandle) {
            return;
        }

        $config = new Config();

        Event::trigger(
            UpdateConfigEvent::class,
            UpdateConfigEvent::EVENT_DEXTER_UPDATE_CONFIG,
            new UpdateConfigEvent([
                'config' => $config,
                'element' => $category,
            ])
        );

        $indices = $config->get('indices.categories');
        $indexName = $indices[$groupHandle] ?? null;

        if (!$indexName) {
            return;
        }

        $command = new DeleteCategoryCommand(
            indexName: $indexName,
            id: $category->uid,
            title: $category->title ?? '',
            queueJobName: DeleteCategoryJob::class,
        );

        /** @var IndexProvider $indexer */
        $indexer = IndexerFactory::create();
        /** @var IndexerResponse $response */
        $response = $indexer->delete($command, $config->get('useQueue'));
    }
}


