<?php

declare(strict_types=1);

namespace boldminded\dexter\events;

use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use yii\base\Event;

class Navigation
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
    }
}


