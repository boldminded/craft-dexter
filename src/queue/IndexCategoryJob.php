<?php

declare(strict_types=1);

namespace boldminded\dexter\queue;

use boldminded\dexter\events\UpdateConfigEvent;
use boldminded\dexter\services\CategoryPipelines;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\IndexableCategory;
use boldminded\dexter\services\IndexerFactory;
use Craft;
use craft\elements\Category;
use craft\queue\BaseJob;
use BoldMinded\DexterCore\Service\Indexer\IndexCategoryCommand;
use BoldMinded\DexterCore\Service\Indexer\IndexerResponse;
use BoldMinded\DexterCore\Service\Indexer\IndexProvider;
use yii\base\Event;
use yii\queue\RetryableJobInterface;

class IndexCategoryJob extends BaseJob implements RetryableJobInterface
{
    public string $uid;
    public string $title = '';
    public array $payload = [];

    public function execute($queue): void
    {
        $siteId = $this->payload['siteId'] ?? null;

        $category = Category::find()
            ->uid($this->uid)
            ->siteId($siteId)
            ->one();

        $categoryGroup = $category->group?->handle;

        if (!$categoryGroup) {
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
        $indexName = $indices[$categoryGroup] ?? null;

        if (!$indexName) {
            return;
        }

        $command = new IndexCategoryCommand(
            indexName: $indexName,
            indexable: new IndexableCategory($category),
            config: $config,
            pipelines: CategoryPipelines::getPipelines($config),
            queueJobName: IndexCategoryJob::class,
        );

        /** @var IndexProvider $indexer */
        $indexer = IndexerFactory::create();
        /** @var IndexerResponse $response */
        $indexer->single($command, false);
    }

    protected function defaultDescription(): string
    {
        return Craft::t('dexter',
            'Indexing category: {title}', [
                'title' => $this->title,
            ]
        );
    }

    public function getTtr()
    {
        // TODO: Implement getTtr() method.
    }

    public function canRetry($attempt, $error)
    {
        // TODO: Implement canRetry() method.
    }
}
