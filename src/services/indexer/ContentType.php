<?php

namespace boldminded\dexter\services\indexer;

use craft\base\Element;

interface ContentType
{
    public static function getPrefix(): string;

    public static function getName(Element $entity): string;

    /** @return array{id: int, title: string} */
    public static function getAll(): array;
}
