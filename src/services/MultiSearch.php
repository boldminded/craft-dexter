<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use BoldMinded\DexterCore\Service\Search\Normalizer;

class MultiSearch
{
    public function __invoke(array $params): array
    {
        $provider = SearchFactory::create();

        $query = Normalizer::searchQuery($params);
        $queries = $params['queries'] ?? [];
        $federation = $params['federation'] ?? [];
        $limit = $params['limit'] ?? 100;
        $idsOnly = $params['idsOnly'] ?? false;

        $results = $provider->multiSearch(
            $queries,
            $query,
            $federation,
            $limit
        );

        if ($idsOnly) {
            return array_column($results, 'uid');
        }

        return $results;
    }
}
