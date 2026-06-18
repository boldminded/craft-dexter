<?php

namespace boldminded\dexter\controllers\routes;

use boldminded\dexter\controllers\FilePath;
use boldminded\dexter\controllers\FileWriter;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\IndexerFactory;
use Craft;

class ExportSettings
{
    public function process(string $indexName, Config $config): bool
    {
        if (empty($_POST)) {
            return false;
        }

        if (!$indexName) {
            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterError',
                    Craft::t('dexter','No index selected.')
                );

            return false;
        }

        $indexer = IndexerFactory::create();

        // Security: the export filename is derived from this value, so a traversal payload
        // (e.g. "../../../path/to/target") would let FileWriter write outside the config directory.
        // Only allow exporting an index that actually exists in the provider's index list.
        $knownIndices = array_column($indexer->list(), 'indexName');

        if (!in_array($indexName, $knownIndices, true)) {
            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterError',
                    Craft::t('dexter', 'Invalid index selected.')
                );

            return false;
        }

        $settings = $indexer->export($indexName);

        if (!is_array($settings) && empty($settings)) {
            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterError',
                    Craft::t('dexter','Invalid settings, could not complete export.')
                );

            return false;
        }

        $filePath = FilePath::make($indexName);

        $success = FileWriter::write($filePath, json_encode($settings, JSON_PRETTY_PRINT));

        if (!$success) {
            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterError',
                    Craft::t('dexter',
                        'There was an error attempting to export settings from {provider}', [
                            'provider' => $config->get('provider'),
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
                    'Index exported successfully to {path}/{provider}/{indexName}.json', [
                        'path' => Craft::getAlias('@config'),
                        'provider' => $config->get('provider'),
                        'indexName' => $indexName,
                    ]
                )
            );

        return true;
    }
}
