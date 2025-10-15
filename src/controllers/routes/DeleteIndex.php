<?php

namespace boldminded\dexter\controllers\routes;

use boldminded\dexter\services\IndexerFactory;
use Craft;

class DeleteIndex
{
    public function process(string $indexName): bool
    {
        if ($indexName === '') {
            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterError',
                    Craft::t('dexter','Whoops! Looks like you need to choose an index to delete.')
                );

            return false;
        }

        $indexer = IndexerFactory::create();

        $result = $indexer->deleteIndex($indexName);

        if ($result->isSuccess()) {
            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterNotice',
                    Craft::t('dexter',
                        'Index <code>{indexName}</code> deleted successfully.', [
                            'indexName' => $indexName,
                        ]
                    )
                );

            return true;
        }

        Craft::$app
            ->getSession()
            ->setFlash(
                'dexterError',
                $result->getErrors()
            );

        return false;
    }
}
