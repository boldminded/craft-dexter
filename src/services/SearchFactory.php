<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use Craft;
use Litzinger\DexterCore\Service\Search\Algolia as AlgoliaSearch;
use Litzinger\DexterCore\Service\Search\Meilisearch as MeilisearchSearch;
use Litzinger\DexterCore\Service\Search\SearchProvider;

class SearchFactory
{
    public static function create(): SearchProvider
    {
        $config = new Config();
        $providerName = $config->get('provider');

        return match ($providerName) {
            'meilisearch' => new MeilisearchSearch(
                MeilisearchClientFactory::create($config),
                $config,
                LoggerFactory::create(Craft::getLogger()),
            ),

            'algolia' => new AlgoliaSearch(
                AlgoliaClientFactory::create($config),
                $config,
                LoggerFactory::create(Craft::getLogger()),
            ),
        };
    }
}
