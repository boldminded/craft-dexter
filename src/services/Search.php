<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use BoldMinded\DexterCore\Service\Search\Normalizer;

class Search
{
    public function __invoke(array $params): array
    {
        $provider = SearchFactory::create($params['provider'] ?? '');

        $index = Normalizer::indexName($params);

        if (!$index) {
            throw new \Exception('Must specify an index to search');
        }

        $term = Normalizer::searchQuery($params);
        $perPage = $params['perPage'] ?? 50;
        $searchParams = $params['searchParams'] ?? [];
        $idsOnly = $params['idsOnly'] ?? false;

        $results = $provider->search(
            $index,
            $term,
            $searchParams,
            $perPage,
        );

        if ($idsOnly) {
            return array_column($results, 'uid');
        }

        return $results;
    }
}
