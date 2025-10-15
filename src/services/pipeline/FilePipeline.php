<?php

declare(strict_types=1);

namespace boldminded\dexter\services\pipeline;

use boldminded\dexter\services\Filterable;
use BoldMinded\DexterCore\Contracts\ConfigInterface;
use BoldMinded\DexterCore\Contracts\IndexableInterface;

class FilePipeline
{
    use Filterable;

    public function __construct(
        private IndexableInterface $indexable,
        private ConfigInterface    $config
    ) {
    }

    public function __invoke(array $values): array
    {
        if (empty($values)) {
            return [];
        }

        $values = $this->filterValues(
            'fileIndexableProperties',
            $values,
        );

        return $values;
    }
}
