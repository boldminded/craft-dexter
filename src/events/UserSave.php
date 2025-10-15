<?php

declare(strict_types=1);

namespace boldminded\dexter\events;

use boldminded\dexter\queue\IndexUserJob;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\IndexableUser;
use boldminded\dexter\services\IndexerFactory;
use boldminded\dexter\services\UserPipelines;
use Craft;
use craft\base\Element;
use craft\elements\User;
use craft\events\ModelEvent;
use craft\helpers\ElementHelper;
use Litzinger\DexterCore\Service\Indexer\IndexerResponse;
use Litzinger\DexterCore\Service\Indexer\IndexProvider;
use Litzinger\DexterCore\Service\Indexer\IndexUserCommand;
use yii\base\Event;

class UserSave
{
    public function subscribe()
    {
        Event::on(
            User::class,
            Element::EVENT_AFTER_SAVE,
            function(ModelEvent $event) {
                try {
                    /** @var User $user */
                    $user = $event->sender;

                    if (
                        $event->sender->resaving ||
                        ElementHelper::isDraft($user) ||
                        ElementHelper::isRevision($user)
                    ) {
                        return;
                    }

                    $this->handleSaveEvent($user);
                } catch (\Throwable $e) {
                    $message = Craft::t('dexter',
                        'Error indexing user: {msg}', [
                            'msg' => $e->getMessage(),
                        ]
                    );

                    Craft::error($message, __METHOD__);

                    Craft::$app
                        ->getSession()
                        ->setFlash(
                            'dexterError',
                            $message
                        );
                }
            }
        );
    }

    public function handleSaveEvent(User $user)
    {
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
        $response = $indexer->single($command, $config->get('useQueue'));
    }
}


