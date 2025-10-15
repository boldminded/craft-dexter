<?php

namespace boldminded\dexter\services\indexer;


use boldminded\dexter\queue\IndexCategoryJob;
use boldminded\dexter\queue\IndexUserJob;
use boldminded\dexter\services\CategoryPipelines;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\IndexableCategory;
use boldminded\dexter\services\IndexableUser;
use boldminded\dexter\services\IndexerFactory;
use boldminded\dexter\services\UserPipelines;
use Craft;
use craft\elements\Category;
use craft\elements\User;
use Litzinger\DexterCore\Service\Indexer\IndexCategoryCommand;
use Litzinger\DexterCore\Service\Indexer\IndexCommandCollection;
use Litzinger\DexterCore\Service\Indexer\IndexUserCommand;
use Litzinger\DexterCore\Service\Indexer\ReIndexCommands;

class ReIndexCommandsCategoryGroup implements ReIndexCommands
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
        $query = Category::find()
            ->group($this->sourceId)
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

        $categories = $query->all();

        $config = new Config();
        $indices = $config->get('indices.categories');
        $indexer = IndexerFactory::create();
        $this->indexName = $indices[$this->sourceId] ?? '[unknown]';

        if (boolval($clear) === true) {
            $response = $indexer->clear($this->indexName);

            if ($response->isSuccess()) {
                $this->alerts[] = Craft::t('dexter',
                    '{indexName} has been cleared of all categories.', [
                        'indexName' => $this->indexName
                    ]);
            } else {
                $this->alerts[] = Craft::t('dexter',
                    'Failed to clear {indexName} of all categories: {errors}.', [
                        'indexName' => $this->indexName,
                        'errors' => implode(' ', $response->getErrors()),
                    ]);
            }
        }

        $commands = [];

        foreach ($categories as $category) {
            $command = new IndexCategoryCommand(
                indexName: $this->indexName,
                indexable: new IndexableCategory($category),
                config: $config,
                pipelines: CategoryPipelines::getPipelines($config),
                queueJobName: IndexCategoryJob::class
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
