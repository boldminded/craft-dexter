<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use Algolia\AlgoliaSearch\Api\SearchClient as AlgoliaClient;
use BoldMinded\DexterCore\Contracts\ConfigInterface;

class AlgoliaClientFactory
{
    public static function create(ConfigInterface $config): AlgoliaClient
    {
        $appId = $config->get('algolia.appId');
        $apiKey = $config->get('algolia.apiKey');

        if (!$appId || !$apiKey) {
            throw new \Exception(
                'Algolia appId or appKey not configured. Please check your config/dexter.php file.'
            );
        }

        return AlgoliaClient::create($appId, $apiKey);
    }
}
