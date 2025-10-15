<?php

namespace boldminded\dexter\controllers;

use boldminded\dexter\controllers\routes\DeleteIndex;
use Craft;

class DeleteController extends BaseController
{
    protected int|bool|array $allowAnonymous = false;

    public function actionIndex()
    {
        $request = Craft::$app->getRequest();
        $indexName = $request->getQueryParam('name');

        if ($indexName) {
            (new DeleteIndex)->process($indexName);
        }

        $this->redirect('dexter');
    }
}
