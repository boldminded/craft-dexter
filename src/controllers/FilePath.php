<?php

namespace boldminded\dexter\controllers;

use boldminded\dexter\services\Config;
use Craft;

class FilePath
{
    public static function make(string $indexName): string
    {
        return self::getConfigPath() . $indexName . '.json';
    }

    public static function getConfigPath(): string
    {
        $config = new Config();

        return Craft::getAlias('@config') . '/' .$config->get('provider') . '/';
    }
}
