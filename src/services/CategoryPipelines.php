<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use boldminded\dexter\services\pipeline\CategoryGroupPipeline;
use boldminded\dexter\services\pipeline\CategoryMenusPipeline;
use boldminded\dexter\services\pipeline\CustomFieldsPipeline;
use boldminded\dexter\services\pipeline\FullTextPipeline;
use boldminded\dexter\services\pipeline\StatusCheckerPipeline;
use BoldMinded\DexterCore\Contracts\ConfigInterface;

class CategoryPipelines
{
    public static function getPipelines(ConfigInterface $config): array
    {
        $pipelines = array_merge([
            CustomFieldsPipeline::class,
        ], $config->get('categoryPipelines'));

        // Make sure this one is always last
        $pipelines[] = FullTextPipeline::class;

        return $pipelines;
    }
}
