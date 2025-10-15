<?php

declare(strict_types=1);

namespace boldminded\dexter\services\pipeline;

use BoldMinded\DexterCore\Contracts\ConfigInterface;
use BoldMinded\DexterCore\Contracts\IndexableInterface;

class StatusCheckerPipeline
{
    public function __construct(
        private IndexableInterface $indexable,
        private ConfigInterface $config
    ) {
    }

    public function __invoke(array $values): array
    {
        if (empty($values)) {
            return [];
        }

        $status = $values['status'] ?? null;

        if ($status && !in_array($status, $this->config->get('statuses'))) {
            $values = [];
        }

        return $values;
    }
}
