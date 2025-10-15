<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use boldminded\dexter\services\pipeline\CustomFieldsPipeline;
use boldminded\dexter\services\pipeline\FullTextPipeline;
use Litzinger\DexterCore\Contracts\ConfigInterface;

class UserPipelines
{
    public static function getPipelines(ConfigInterface $config): array
    {
        $pipelines = array_merge([
            CustomFieldsPipeline::class,
        ], $config->get('userPipelines'));

        // Make sure this one is always last
        $pipelines[] = FullTextPipeline::class;

        return $pipelines;
    }
}
