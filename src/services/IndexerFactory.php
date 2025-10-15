<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use Craft;
use Litzinger\DexterCore\Service\Indexer\Algolia as AlgoliaIndexer;
use Litzinger\DexterCore\Service\Indexer\Dummy as DummyIndexer;
use Litzinger\DexterCore\Service\Indexer\IndexProvider;
use Litzinger\DexterCore\Service\Indexer\Meilisearch as MeilisearchIndexer;

class IndexerFactory
{
    public static function create(): IndexProvider
    {
        $config = new Config();
        $providerName = $config->get('provider');
        $shouldUseQueue = $config->get('useQueue');

        return match ($providerName) {
            'meilisearch' => new MeilisearchIndexer(
                MeilisearchClientFactory::create($config),
                $config,
                LoggerFactory::create(Craft::getLogger()),
                QueueFactory::create(Craft::$app->getQueue()),
                TranslatorFactory::create(Craft::$app->getI18n()),
                $shouldUseQueue
            ),

            'algolia' => new AlgoliaIndexer(
                AlgoliaClientFactory::create($config),
                $config,
                LoggerFactory::create(Craft::getLogger()),
                QueueFactory::create(Craft::$app->getQueue()),
                TranslatorFactory::create(Craft::$app->getI18n()),
                $shouldUseQueue
            ),

            default => new DummyIndexer(),
        };
    }
}
