<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use boldminded\dexter\services\pipeline\CategoryGroupPipeline;
use boldminded\dexter\services\pipeline\CategoryMenusPipeline;
use boldminded\dexter\services\pipeline\CustomFieldsPipeline;
use boldminded\dexter\services\pipeline\FullTextPipeline;
use boldminded\dexter\services\pipeline\StatusCheckerPipeline;
use Litzinger\DexterCore\Contracts\ConfigInterface;

class EntryPipelines
{
    public static function getPipelines(ConfigInterface $config): array
    {
        $pipelines = array_merge([
            StatusCheckerPipeline::class,
            CustomFieldsPipeline::class,
            CategoryMenusPipeline::class,
            CategoryGroupPipeline::class,
        ], $config->get('entryPipelines'));

        // Make sure this one is always last
        $pipelines[] = FullTextPipeline::class;

        return $pipelines;
    }
}
