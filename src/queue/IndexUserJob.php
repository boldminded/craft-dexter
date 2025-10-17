<?php

declare(strict_types=1);

namespace boldminded\dexter\queue;

use boldminded\dexter\events\UpdateConfigEvent;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\IndexableUser;
use boldminded\dexter\services\IndexerFactory;
use boldminded\dexter\services\UserPipelines;
use Craft;
use craft\elements\User;
use craft\queue\BaseJob;
use BoldMinded\DexterCore\Service\Indexer\IndexerResponse;
use BoldMinded\DexterCore\Service\Indexer\IndexProvider;
use BoldMinded\DexterCore\Service\Indexer\IndexUserCommand;
use yii\base\Event;
use yii\queue\RetryableJobInterface;

class IndexUserJob extends BaseJob implements RetryableJobInterface
{
    public string $uid;
    public string $title = '';
    public array $payload = [];

    public function execute($queue): void
    {
        $siteId = $this->payload['siteId'] ?? null;

        $user = User::find()
            ->uid($this->uid)
            ->siteId($siteId)
            ->one();

        $userGroups = array_column($user->groups ?? [], 'handle');

        if (empty($userGroups)) {
            return;
        }

        $config = new Config();

        Event::trigger(
            UpdateConfigEvent::class,
            UpdateConfigEvent::EVENT_DEXTER_UPDATE_CONFIG,
            new UpdateConfigEvent([
                'config' => $config,
                'element' => $user,
            ])
        );

        $indices = $config->get('indices.users');

        $indexName = array_reduce($userGroups, function ($carry, $groupHandle) use ($indices) {
            return $indices[$groupHandle] ?? $carry;
        }, null);

        if (!$indexName) {
            return;
        }

        $command = new IndexUserCommand(
            indexName: $indexName,
            indexable: new IndexableUser($user),
            config: $config,
            pipelines: UserPipelines::getPipelines($config),
            queueJobName: IndexUserJob::class,
        );

        /** @var IndexProvider $indexer */
        $indexer = IndexerFactory::create();
        /** @var IndexerResponse $response */
        $indexer->single($command, false);
    }

    protected function defaultDescription(): string
    {
        return Craft::t('dexter',
            'Indexing user: {title}', [
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
