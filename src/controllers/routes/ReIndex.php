<?php

namespace boldminded\dexter\controllers\routes;

use boldminded\dexter\services\Config;
use boldminded\dexter\services\indexer\ReIndexCommandsFactory;
use boldminded\dexter\services\IndexerFactory;
use Craft;

class ReIndex
{
    public function process(string $indexSource): bool
    {
        if ($indexSource === '') {
            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterError',
                    Craft::t('dexter','Whoops! Looks like you need to choose an index to clear.')
                );

            return false;
        }

        $config = new Config();
        $factory = (new ReIndexCommandsFactory())->create($indexSource);
        $commands = $factory->getCommandCollection();
        $totalObjects = $commands->count();

        $indexer = IndexerFactory::create();
        $indexName = $factory->getIndexName();
        $result = $indexer->bulk($commands);

        $alertMessage = $factory->getAlerts();

        if (!empty($alertMessage)) {
            Craft::$app->getSession()->setFlash('error', $alertMessage);
        }

        if ($result->isSuccess()) {
            $shouldUseQueue = $config->get('useQueue');

            if ($shouldUseQueue) {
                $message = Craft::t('dexter',
                    'Queued {totalObjects} objects for reindexing in <code>{indexName}</code>.', [
                        'totalObjects' => $totalObjects,
                        'indexName' => $indexName,
                    ]
                );
            } else {
                $message = Craft::t('dexter',
                    'Reindexed {totalObjects} in <code>{indexName}</code>.', [
                        'totalObjects' => $totalObjects,
                        'indexName' => $indexName,
                    ]
                );
            }

            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterNotice',
                    $message
                );

            return true;
        }

        Craft::$app
            ->getSession()
            ->setFlash(
                'dexterError',
                Craft::t('dexter',
                    'There was an error attempting to reindex {indexName}: {errors}', [
                        'indexName' => $indexName,
                        'errors' => implode(' ', $result->getErrors()),
                    ]
                )
            );

        return false;
    }
}
