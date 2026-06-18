<?php

namespace boldminded\dexter\controllers;

use boldminded\dexter\services\Config;
use Craft;

class FilePath
{
    public static function make(string $indexName): string
    {
        // Security: never let the index name escape the config directory. Strip any path components and
        // restrict to a safe character set so traversal payloads (e.g. "../../foo") cannot be used to write
        // outside the config/<provider>/ directory.
        $indexName = basename($indexName);
        $indexName = preg_replace('/[^A-Za-z0-9_-]/', '', $indexName);

        return self::getConfigPath() . $indexName . '.json';
    }

    public static function getConfigPath(): string
    {
        $config = new Config();

        return Craft::getAlias('@config') . '/' .$config->get('provider') . '/';
    }
}
