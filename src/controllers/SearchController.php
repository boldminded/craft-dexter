<?php

namespace boldminded\dexter\controllers;

use boldminded\dexter\services\Config;
use boldminded\dexter\services\Search;
use BoldMinded\DexterCore\Service\Search\Normalizer;
use Craft;
use craft\web\Controller;
use yii\filters\VerbFilter;
use yii\web\Response;

class SearchController extends Controller
{
    /**
     * Search params a public client is permitted to set. Anything else (e.g. attributesToRetrieve,
     * a raw filter expression, arbitrary provider settings) is dropped before reaching the provider.
     */
    private const ALLOWED_PARAMS = [
        'limit',
        'offset',
        'page',
        'perPage',
        'hitsPerPage',
        'sort',
        'facets',
        'matchingStrategy',
        'attributesToHighlight',
        'hybrid',
    ];

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
        $config = new Config();

        $params = array_merge(
            $request->getQueryParams(),
            $request->getBodyParams(),
        );

        // The only indices a client may search are the ones the developer has explicitly configured under
        // 'indices'. This blocks enumeration of arbitrary index names while leaving the developer in full
        // control: whatever they configure is theirs to expose. The Twig tag (craft.dexter.search) calls the
        // Search service directly and is intentionally NOT restricted here — that path is developer-controlled.
        if (!$this->isConfiguredIndex($config, Normalizer::indexName($params))) {
            return $this->asJson([]);
        }

        // Strip everything the client should not control (raw searchParams, attributesToRetrieve, etc.) and
        // apply a default result limit so an empty query cannot pull an unbounded set.
        $searchParams = $this->sanitizeParams($config, $params['searchParams'] ?? []);

        $results = (new Search)([
            'provider' => $params['provider'] ?? '',
            'index' => Normalizer::indexName($params),
            'query' => Normalizer::searchQuery($params),
            'perPage' => $searchParams['limit'] ?? $this->defaultLimit($config),
            'searchParams' => $searchParams,
            'idsOnly' => $params['idsOnly'] ?? false,
        ]);

        return $this->asJson($results);
    }

    /**
     * True only if the given index is one the developer has configured under any 'indices.*' group.
     * If nothing is configured, nothing is searchable.
     */
    private function isConfiguredIndex(Config $config, string $index): bool
    {
        if ($index === '') {
            return false;
        }

        $configured = array_merge(
            array_values($config->get('indices.entries') ?? []),
            array_values($config->get('indices.files') ?? []),
            array_values($config->get('indices.categories') ?? []),
            array_values($config->get('indices.users') ?? []),
        );

        return in_array($index, $configured, true);
    }

    /**
     * Drop any client-supplied param that is not on the allowlist, apply the configured default limit when
     * the client did not set one, and clamp offset/limit to a safe range.
     */
    private function sanitizeParams(Config $config, mixed $params): array
    {
        if (!is_array($params)) {
            $params = [];
        }

        $params = array_intersect_key($params, array_flip(self::ALLOWED_PARAMS));

        if (!isset($params['limit'])) {
            $params['limit'] = $this->defaultLimit($config);
        } else {
            $params['limit'] = max((int) $params['limit'], 1);
        }

        if (isset($params['offset'])) {
            $params['offset'] = max((int) $params['offset'], 0);
        }

        return $params;
    }

    private function defaultLimit(Config $config): int
    {
        $limit = (int) ($config->get('searchLimit') ?? 100);

        return $limit > 0 ? $limit : 100;
    }

    public function actionGetCsrfToken(): Response
    {
        $request = Craft::$app->getRequest();

        return $this->asJson([
            'token' => $request->getCsrfToken(),
        ]);
    }
}
