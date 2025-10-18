<?php

declare(strict_types=1);

namespace boldminded\dexter\events;

use boldminded\dexter\services\Config;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use yii\base\Event;

class Endpoints
{
    public function subscribe()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['dexter'] = 'dexter/dashboard/index';
                $event->rules['dexter/import-settings'] = 'dexter/import/index';
                $event->rules['dexter/export-settings'] = 'dexter/export/index';
                $event->rules['dexter/clear-index'] = 'dexter/clear/index';
                $event->rules['dexter/delete-index'] = 'dexter/delete/index';
                $event->rules['dexter/re-index'] = 'dexter/re-index/index';
            }
        );

        $config = new Config();
        $endpointUrl = $config->get('endpointUrl') ?: 'dexter/search';

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            static function (RegisterUrlRulesEvent $e) use ($endpointUrl) {
                $e->rules[$endpointUrl] = 'dexter/search/index';
                $e->rules['dexter/get-csrf-token'] = 'dexter/search/get-csrf-token';
            }
        );
    }
}


