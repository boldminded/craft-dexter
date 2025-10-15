<?php

namespace boldminded\dexter\controllers;

use boldminded\dexter\controllers\routes\ImportSettings;
use Craft;
use craft\web\View;
use yii\web\Response;

class ImportController extends BaseController
{
    protected int|bool|array $allowAnonymous = false;

    public function actionIndex(): Response
    {
        $request = Craft::$app->getRequest();
        $data = $request->getBodyParams();
        $indexSource = $data['importSource'] ?? '';
        $settingsPath = $data['importSettings'] ?? '';

        if (!empty($data)) {
            (new ImportSettings)->process($indexSource, $settingsPath, $this->config);
        }

        $files = DirectoryReader::read(FilePath::getConfigPath());
        $choices = IndexChoices::asOptionGroups();

        $vars = [
            'title' => 'Dexter',
            'navItems' => $this->getNav(),
            'choices' => $choices,
            'configPath' => Craft::getAlias('@config') . '/' .$this->config->get('provider'),
            'files' => $files,
            'providerName' => $this->config->getProviderName(),
            'selectedNavItem' => 'import-settings',
        ];

        return $this->renderTemplate('dexter/import-settings', $vars, View::TEMPLATE_MODE_CP);
    }
}
