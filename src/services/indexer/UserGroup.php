<?php

namespace boldminded\dexter\services\indexer;

use Craft;
use craft\base\Element;
use craft\models\UserGroup as CraftUserGroup;

class UserGroup implements ContentType
{
    const PREFIX = 'users.';

    public static function getPrefix(): string
    {
        return self::PREFIX;
    }

    public static function getName(Element $entity): string
    {
        assert($entity instanceof CraftUserGroup);

        return self::PREFIX . $entity->group->handle;
    }

    public static function getAll(): array
    {
        $allUserGroups = Craft::$app->userGroups->getAssignableGroups();
        $array = [];

        foreach ($allUserGroups as $group) {
            $array[$group->handle] = [
                'handle' => $group->handle,
                'name' => $group->name,
            ];
        }

        return $array;
    }
}
