<?php

namespace boldminded\dexter\controllers;

use boldminded\dexter\controllers\routes\ExportSettings;
use boldminded\dexter\services\IndexerFactory;
use Craft;
use craft\web\View;
use yii\web\Response;

class ExportController extends BaseController
{
    protected int|bool|array $allowAnonymous = false;

    public function actionIndex(): Response
    {
        $request = Craft::$app->getRequest();
        $data = $request->getBodyParams();
        $indexName = $data['exportIndex'] ?? '';

        if (!empty($data)) {
            (new ExportSettings)->process($indexName, $this->config);
        }

        $indexer = IndexerFactory::create();
        $indices = array_column($indexer->list(), 'indexName');
        $choices = array_combine(array_values($indices), array_values($indices));

        $vars = [
            'title' => 'Dexter',
            'navItems' => $this->getNav(),
            'choices' => $choices,
            'configPath' => Craft::getAlias('@config') . '/' .$this->config->get('provider'),
            'providerName' => $this->config->getProviderName(),
            'selectedNavItem' => 'export-settings',
        ];

        return $this->renderTemplate('dexter/export-settings', $vars, View::TEMPLATE_MODE_CP);
    }
}
