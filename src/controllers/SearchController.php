<?php

namespace boldminded\dexter\controllers;

use boldminded\dexter\services\Config;
use boldminded\dexter\services\Search;
use Craft;
use craft\web\Controller;
use yii\filters\VerbFilter;
use yii\web\Response;

class SearchController extends Controller
{
    protected int|bool|array $allowAnonymous = [
        'index' => self::ALLOW_ANONYMOUS_LIVE,
        'get-csrf-token' => self::ALLOW_ANONYMOUS_LIVE,
    ];

    public $enableCsrfValidation = false;

    public function init(): void
    {
        parent::init();

        $this->handleSecureSearch();
    }

    private function handleSecureSearch(): void
    {
        $config = new Config();

        $useSecureSearch = $config->get('secureSearch');

        if ($useSecureSearch) {
            $this->enableCsrfValidation = true;

            $headers = Craft::$app->getResponse()->getHeaders();
            $headers->set('Access-Control-Allow-Origin', $_ENV['DEFAULT_SITE_URL']);
            $headers->set('Access-Control-Allow-Methods', 'GET, POST');
            $headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-CSRF-Token');
        }
    }

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['GET', 'POST'],
            ],
        ];
        return $behaviors;
    }

    public function actionIndex(): Response
    {
        $request = Craft::$app->getRequest();

        $params = array_merge(
            $request->getQueryParams(),
            $request->getBodyParams(),
        );

        $results = (new Search)($params);

        return $this->asJson($results);
    }

    public function actionGetCsrfToken(): Response
    {
        $request = Craft::$app->getRequest();

        return $this->asJson([
            'token' => $request->getCsrfToken(),
        ]);
    }
}
