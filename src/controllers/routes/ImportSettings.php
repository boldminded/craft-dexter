<?php

namespace boldminded\dexter\controllers\routes;

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
}
