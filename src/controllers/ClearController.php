<?php

namespace boldminded\dexter\controllers;

use boldminded\dexter\controllers\routes\ClearIndex;
use Craft;

class ClearController extends BaseController
{
    protected int|bool|array $allowAnonymous = false;

    public function actionIndex()
    {
        $request = Craft::$app->getRequest();
        $indexName = $request->getQueryParam('name');

        if ($indexName) {
            (new ClearIndex)->process($indexName);
        }

        $this->redirect('dexter');
    }
}
