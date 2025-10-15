<?php

namespace boldminded\dexter\controllers\routes;

use boldminded\dexter\services\IndexerFactory;
use Craft;

class ClearIndex
{
    public function process(string $indexName): bool
    {
        if ($indexName === '') {
            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterError',
                    Craft::t('dexter','Whoops! Looks like you need to choose an index to clear.')
                );

            return false;
        }

        $indexer = IndexerFactory::create();

        $result = $indexer->clear($indexName);

        if ($result->isSuccess()) {
            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterNotice',
                    Craft::t('dexter',
                        'Index <code>{indexName}</code> cleared successfully.', [
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
