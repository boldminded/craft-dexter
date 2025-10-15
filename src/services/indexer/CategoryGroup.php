<?php

namespace boldminded\dexter\services\indexer;

use Craft;
use craft\base\Element;
use craft\elements\Category;

class CategoryGroup implements ContentType
{
    const PREFIX = 'categories.';

    public static function getPrefix(): string
    {
        return self::PREFIX;
    }

    public static function getName(Element $entity): string
    {
        assert($entity instanceof Category);

        return self::PREFIX . $entity->group->handle;
    }

    public static function getAll(): array
    {
        $allCategoryGroups = Craft::$app->categories->getAllGroups();
        $array = [];

        foreach ($allCategoryGroups as $group) {
            $array[$group->handle] = [
                'handle' => $group->handle,
                'name' => $group->name,
            ];
        }

        return $array;
    }
}
