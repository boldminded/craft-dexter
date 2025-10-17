<?php

declare(strict_types=1);

namespace boldminded\dexter\queue;

use boldminded\dexter\events\UpdateConfigEvent;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\FileUpdater;
use boldminded\dexter\services\IndexableFile;
use Craft;
use craft\elements\Asset;
use craft\queue\BaseJob;
use yii\base\Event;
use yii\queue\RetryableJobInterface;

class UpdateFileJob extends BaseJob implements RetryableJobInterface
{
    public string $uid;
    public string $title = '';
    public array $payload = [];

    public function execute($queue): void
    {
        $siteId = $this->payload['siteId'] ?? null;

        $file = Asset::find()
            ->uid($this->uid)
            ->siteId($siteId)
            ->one();

        if (!$file) {
            return;
        }

        $indexable = new IndexableFile($file);
        $config = new Config();

        Event::trigger(
            UpdateConfigEvent::class,
            UpdateConfigEvent::EVENT_DEXTER_UPDATE_CONFIG,
            new UpdateConfigEvent([
                'config' => $config,
                'element' => $file,
            ])
        );

        (new FileUpdater($indexable, $config))->update($this->payload);
    }

    protected function defaultDescription(): string
    {
        return Craft::t('dexter',
            'Updating file: {title}', [
                'title' => $this->title,
            ]
        );
    }

    public function getTtr()
    {
        // TODO: Implement getTtr() method.
    }

    public function canRetry($attempt, $error)
    {
        // TODO: Implement canRetry() method.
    }
}
