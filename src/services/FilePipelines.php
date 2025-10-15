<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use boldminded\dexter\services\pipeline\CategoryGroupPipeline;
use boldminded\dexter\services\pipeline\CategoryMenusPipeline;
use boldminded\dexter\services\pipeline\CustomFieldsPipeline;
use boldminded\dexter\services\pipeline\FileDescribePipeline;
use boldminded\dexter\services\pipeline\FilePipeline;
use boldminded\dexter\services\pipeline\FullTextPipeline;
use BoldMinded\DexterCore\Contracts\ConfigInterface;

class FilePipelines
{
    public static function getPipelines(ConfigInterface $config): array
    {
        $pipelines = array_merge([
            //FilePipeline::class,
            CustomFieldsPipeline::class,
            CategoryMenusPipeline::class,
            CategoryGroupPipeline::class,
            FileDescribePipeline::class,
        ], $config->get('filePipelines'));

        // Make sure this one is always last
        $pipelines[] = FullTextPipeline::class;

        return $pipelines;
    }
}
