<?php

declare(strict_types=1);

namespace boldminded\dexter\queue;

use boldminded\dexter\services\CategoryPipelines;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\IndexableCategory;
use boldminded\dexter\services\IndexerFactory;
use Craft;
use craft\elements\Category;
use craft\queue\BaseJob;
use Litzinger\DexterCore\Service\Indexer\IndexCategoryCommand;
use Litzinger\DexterCore\Service\Indexer\IndexerResponse;
use Litzinger\DexterCore\Service\Indexer\IndexProvider;
use yii\queue\RetryableJobInterface;

class IndexCategoryJob extends BaseJob implements RetryableJobInterface
{
    public string $uid;
    public string $title = '';
    public array $payload = [];

    public function execute($queue): void
    {
        $category = Category::find()
            ->uid($this->uid)
            ->one();

        $categoryGroup = $category->group?->handle;

        if (!$categoryGroup) {
            return;
        }

        $config = new Config();
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
