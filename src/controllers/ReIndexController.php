<?php

namespace boldminded\dexter\controllers;

use boldminded\dexter\controllers\routes\ReIndex;
use Craft;

class ReIndexController extends BaseController
{
    protected int|bool|array $allowAnonymous = false;

    public function actionIndex()
    {
        $request = Craft::$app->getRequest();
        $data = $request->getBodyParams();
        $indexSource = $data['indexSource'] ?? '';
        $siteId = $data['siteId'] ? (int) $data['siteId'] : null;
        $clear = $data['clear'] ?? false;

        if (!$indexSource) {
            $this->redirect('dexter');
        }

        (new ReIndex)->process($indexSource, $siteId, $clear);

        $this->redirect('dexter');
    }
}
