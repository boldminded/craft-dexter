<?php

declare(strict_types=1);

namespace boldminded\dexter\events;

use boldminded\dexter\queue\DeleteUserJob;
use boldminded\dexter\services\Config;
use boldminded\dexter\services\IndexerFactory;
use Craft;
use craft\base\Element;
use craft\elements\User;
use craft\helpers\ElementHelper;
use Litzinger\DexterCore\Service\Indexer\DeleteFileCommand;
use Litzinger\DexterCore\Service\Indexer\IndexerResponse;
use Litzinger\DexterCore\Service\Indexer\IndexProvider;
use yii\base\Event;

class UserDelete
{
    public function subscribe()
    {
        Event::on(
            User::class,
            Element::EVENT_AFTER_DELETE,
            function(Event $event) {
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

                    $this->handleDeleteEvent($user);
                } catch (\Throwable $e) {
                    $message = Craft::t('dexter',
                        'Error deleting user: {msg}', [
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

    public function handleDeleteEvent(User $user)
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

        $command = new DeleteFileCommand(
            indexName: $indexName,
            id: $user->uid,
            title: $user->username ?? '',
            queueJobName: DeleteUserJob::class,
        );

        /** @var IndexProvider $indexer */
        $indexer = IndexerFactory::create();
        /** @var IndexerResponse $response */
        $response = $indexer->delete($command, $config->get('useQueue'));
    }
}


