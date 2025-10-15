<?php

namespace boldminded\dexter\services\indexer;

use Craft;
use craft\base\Element;
use craft\elements\Entry;

class Section implements ContentType
{
    const PREFIX = 'entries.';

    public static function getPrefix(): string
    {
        return self::PREFIX;
    }

    public static function getName(Element $entity): string
    {
        assert($entity instanceof Entry);

        return self::PREFIX . $entity->type->handle;
    }

    public static function getAll(): array
    {
        $allSections = Craft::$app->entries->getAllSections();
        $entryTypesArray = [];

        foreach ($allSections as $section) {
            $entryTypes = $section->getEntryTypes();

            foreach ($entryTypes as $type) {
                $entryTypesArray[$type->handle] = [
                    'handle' => $type->handle,
                    'name' => $type->name,
                ];
            }
        }

        return $entryTypesArray;
    }
}
