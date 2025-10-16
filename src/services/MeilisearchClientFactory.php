<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use BoldMinded\DexterCore\Contracts\ConfigInterface;
use Meilisearch\Client;

class MeilisearchClientFactory
{
    public static function create(ConfigInterface $config): Client
    {
        $appKey = $config->get('meilisearch.appKey');
        $url = $config->get('meilisearch.url');

        if (!$appKey || !$url) {
            throw new \Exception(
                'Meilisearch appKey or url not configured. Please check your config/dexter.php file.'
            );
        }

        return (new Client($url, $appKey));
    }
}
