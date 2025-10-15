<?php

namespace boldminded\dexter\controllers;

use boldminded\dexter\services\Config;
use Craft;
use craft\web\Controller;

class BaseController extends Controller
{
    protected int|bool|array $allowAnonymous = false;

    protected Config $config;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->config = new Config();
    }

    protected function getNav(): array
    {
        return [
            '' => ['title' => Craft::t('dexter', 'Dashboard')],
            'import-settings' => ['title' => Craft::t('dexter', 'Import Settings')],
            'export-settings' => ['title' => Craft::t('dexter', 'Export Settings')],
            'https://docs.boldminded.com/dexter-craft' => ['title' => Craft::t('dexter', 'Documentation'), 'external' => true],
        ];
    }
}
