<?php

namespace boldminded\dexter\controllers\routes;

use boldminded\dexter\controllers\DirectoryReader;
use boldminded\dexter\controllers\FilePath;
use boldminded\dexter\controllers\FileReader;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\IndexerFactory;
use Craft;

class ImportSettings
{
    public function process(string $indexSource, string $settingsPath, Config $config): bool
    {
        if (empty($_POST)) {
            return false;
        }

        if (!$indexSource) {
            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterError',
                    Craft::t('dexter','No index selected.')
                );

            return false;
        }

        if (!$settingsPath) {
            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterError',
                    Craft::t('dexter','No settings file selected.')
                );

            return false;
        }

        // Security: never trust the submitted path. The dropdown only offers files inside the config
        // directory, but a forged request could point importSettings at any JSON-parseable file on disk.
        // Re-validate that the submitted path is one of the files actually present in the config directory.
        if (!$this->isAllowedSettingsFile($settingsPath)) {
            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterError',
                    Craft::t('dexter', 'Invalid settings file selected.')
                );

            return false;
        }

        $parts = explode('.', $indexSource, 2);
        $prefix = $parts[0];
        $index = $parts[1];
        $indices = $config->get('indices.' . $prefix);
        $indexName = $indices[$index] ?? '[unknown]';

        $settings = FileReader::readJson($settingsPath);

        if (!is_array($settings) && empty($settings)) {
            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterError',
                    'Invalid settings, could not complete import.'
                );
        }

        $indexer = IndexerFactory::create();
        $success = $indexer->import($indexName, $settings);

        if (!$success) {
            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterError',
                    Craft::t('dexter',
                        'There was an error attempting to import settings into <code>{indexName}</code>.', [
                            'indexName' => $indexName,
                        ]
                    )
                );

            return false;
        }

        Craft::$app
            ->getSession()
            ->setFlash(
                'dexterNotice',
                Craft::t('dexter',
                    'Settings were successfully imported into <code>{indexName}</code>.', [
                        'indexName' => $indexName,
                    ]
                )
            );

        return true;
    }

    /**
     * Confirm the submitted settings file path resolves to a file that actually lives inside the plugin's
     * config directory. Guards against path traversal / arbitrary file reads from a forged POST.
     */
    private function isAllowedSettingsFile(string $settingsPath): bool
    {
        $configPath = realpath(FilePath::getConfigPath());

        if ($configPath === false) {
            return false;
        }

        $realPath = realpath($settingsPath);

        if ($realPath === false || !is_file($realPath)) {
            return false;
        }

        // Containment check: the resolved file must sit within the resolved config directory.
        $configPath = rtrim($configPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (strncmp($realPath, $configPath, strlen($configPath)) !== 0) {
            return false;
        }

        // Allowlist check: the path must be one of the files the directory listing offered.
        $allowed = array_keys(DirectoryReader::read(FilePath::getConfigPath()));
        foreach ($allowed as $allowedPath) {
            if (realpath($allowedPath) === $realPath) {
                return true;
            }
        }

        return false;
    }
}
