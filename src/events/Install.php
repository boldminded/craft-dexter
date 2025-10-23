<?php

declare(strict_types=1);

namespace boldminded\dexter\events;

use boldminded\dexter\controllers\FileWriter;
use boldminded\dexter\Dexter;
use Craft;
use craft\events\PluginEvent;
use craft\services\Plugins;
use yii\base\Event;

class Install
{
    public function subscribe(): void
    {
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin instanceof Dexter) {
                    $this->handleInstallEvent();
                }
            }
        );
    }

    private function handleInstallEvent(): void
    {
        $configFilePath = Craft::getAlias('@config') . '/dexter.php';

        if (!file_exists($configFilePath)) {
            FileWriter::write($configFilePath, $this->getDefaultConfig());
        }
    }

    private function getDefaultConfig(): string
    {
        $siteUrl = Craft::$app->getSites()->getCurrentSite()->getBaseUrl();
        $vendorPath = Craft::getAlias('@vendor') . '/boldminded/craft-dexter/src/settings.php';

        return <<<EOL
<?php
/*
 * This is the minimum configuration required for Dexter to work, assuming you're using DDEV with
 * a local instance of Meilisearch. Refer to the documentation at https://docs.boldminded.com/dexter/docs-craft/configuration
 * or the $vendorPath file for more configuration options.
 */

return [
    // Choose which provider you're using.
    'provider' => 'meilisearch', // or "algolia"

    'meilisearch' => [
        'url' => '$siteUrl:7700',
        'appKey' => 'ddev',
    ],

    'algolia' => [
        'appId' => '',
        'apiKey' => '',
        'mode' => 'keywordSearch', // or neuralSearch if using the Premium or Elevate plans.
    ],
];
EOL;
    }
}
