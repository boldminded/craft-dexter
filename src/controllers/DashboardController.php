<?php

namespace boldminded\dexter\controllers;

use boldminded\dexter\services\IndexerFactory;
use Craft;
use craft\web\View;
use yii\web\Response;

class DashboardController extends BaseController
{
    protected int|bool|array $allowAnonymous = false;

    public function actionIndex(): Response
    {
        $indexer = IndexerFactory::create();

        $vars = [
            'title' => 'Dexter',
            'navItems' => $this->getNav(),
            'siteChoices' => array_map(fn ($site) => [
                'label' => $site->name,
                'value' => $site->id,
            ], Craft::$app->sites->getAllSites()),
            'indexChoices' => IndexChoices::asOptionGroups(),
            'indices' => $indexer->list(),
            'providerName' => $this->config->getProviderName(),
            'selectedNavItem' => '',
        ];

        return $this->renderTemplate('dexter/index', $vars, View::TEMPLATE_MODE_CP);
    }
}
