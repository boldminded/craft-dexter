<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use Craft;
use Litzinger\DexterCore\Contracts\ConfigInterface;
use Litzinger\DexterCore\Service\Config as DexterConfig;
use Litzinger\DexterCore\Service\ConfigFile;

class Config implements ConfigInterface
{
    private DexterConfig $config;

    public function __construct()
    {
        $configFile = new ConfigFile(dirname(__DIR__) . '/');
        $this->config = new DexterConfig($configFile, $this->loadFromFile());
    }

    public function get(string $key, ?string $index = null, ?array $values = null): mixed
    {
        return $this->config->get($key, $index, $values);
    }

    public function getProviderName(): string
    {
        return match ($this->get('provider')) {
            'meilisearch' => 'Meilisearch',
            'algolia' => 'Algolia',
            default => 'Dummy',
        };
    }

    public function getAll(): array
    {
        return $this->config->getAll();
    }

    public function setAll(array $options): void
    {
        $this->config->setAll($options);
    }

    private function loadFromFile(): array
    {
        $filePath = Craft::getAlias('@config') . '/dexter.php';

        if (file_exists($filePath)) {
            return require $filePath;
        }

        return [];
    }
}
