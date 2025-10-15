<?php

declare(strict_types=1);

namespace boldminded\dexter\queue;

use boldminded\dexter\services\Config;
use boldminded\dexter\services\IndexableUser;
use boldminded\dexter\services\IndexerFactory;
use boldminded\dexter\services\UserPipelines;
use Craft;
use craft\elements\User;
use craft\queue\BaseJob;
use Litzinger\DexterCore\Service\Indexer\IndexerResponse;
use Litzinger\DexterCore\Service\Indexer\IndexProvider;
use Litzinger\DexterCore\Service\Indexer\IndexUserCommand;
use yii\queue\RetryableJobInterface;

class IndexUserJob extends BaseJob implements RetryableJobInterface
{
    public string $uid;
    public string $title = '';
    public array $payload = [];

    public function execute($queue): void
    {
        $user = User::find()
            ->uid($this->uid)
            ->one();

        $userGroups = array_column($user->groups ?? [], 'handle');

        if (empty($userGroups)) {
            return;
        }

        $config = new Config();
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
