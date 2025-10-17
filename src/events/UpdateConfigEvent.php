<?php

declare(strict_types=1);

namespace boldminded\dexter\events;

use boldminded\dexter\services\Config;
use boldminded\dexter\services\Suffix;
use craft\base\Element;
use yii\base\Event;

class UpdateConfigEvent extends Event
{
    public const EVENT_DEXTER_UPDATE_CONFIG = 'dexterUpdateConfig';

    public Config $config;
    public Element|null $element = null;
    public int|null $siteId = null;
    public string|null $uid = null;

    public function subscribe(): void
    {
        Event::on(
            self::class,
            self::EVENT_DEXTER_UPDATE_CONFIG,
            function (UpdateConfigEvent $event) {
                $config = $event->config;

                if ($event->config->get('appendSiteSuffix') !== true) {
                    return;
                }

                $configArray = $config->getAll();

                if ($event->uid) {
                    $configArray['suffix'] = Suffix::getByUid($event->uid);
                }

                if ($event->element) {
                    $configArray['suffix'] = Suffix::getByElement($event->element);
                }

                if ($event->siteId) {
                    $configArray['suffix'] = Suffix::getBySiteId($event->siteId);
                }

                $config->setAll($configArray);
            }
        );
    }

}
