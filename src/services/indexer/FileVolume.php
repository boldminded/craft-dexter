<?php

namespace boldminded\dexter\services\indexer;

use Craft;
use craft\base\Element;
use craft\elements\Asset;

class FileVolume implements ContentType
{
    const PREFIX = 'files.';

    public static function getPrefix(): string
    {
        return self::PREFIX;
    }

    public static function getName(Element $entity): string
    {
        assert($entity instanceof Asset);

        return self::PREFIX . $entity->volume->handle;
    }

    public static function getAll(): array
    {
        $allFileVolumes = Craft::$app->volumes->getAllVolumes();
        $array = [];

        foreach ($allFileVolumes as $volume) {
            $array[$volume->handle] = [
                'handle' => $volume->handle,
                'name' => $volume->name,
            ];
        }

        return $array;
    }
}
