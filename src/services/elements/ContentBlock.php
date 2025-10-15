<?php

declare(strict_types=1);

namespace boldminded\dexter\services\elements;

use boldminded\dexter\services\Filterable;
use craft\base\Element;
use craft\elements\ContentBlock as ContentBlockElement;
use Litzinger\DexterCore\Contracts\ConfigInterface;
use Litzinger\DexterCore\Contracts\IndexableInterface;

class ContentBlock implements ElementInterface
{
    use Filterable;

    public bool $returnsMultipleValues = false;

    public function __construct(
        protected ConfigInterface $config
    ) {
    }

    public function getClassName(): string
    {
        return 'craft\elements\ContentBlock';
    }

    public function getValue(
        string $fieldHandle,
        IndexableInterface $indexable
    ): mixed
    {
        return $this->serialize($indexable->getEntity(), $fieldHandle);
    }

    private function serialize(Element $entry, string $fieldHandle): array
    {
        $block = $entry->getFieldValue($fieldHandle);

        return $this->toArray($block);
    }

    private function toArray(ContentBlockElement $block): array
    {
        return array_merge($block->toArray([
                'id',
                'uid',
                'siteId',
            ]), [
                'dateCreated' => $block->dateCreated?->getTimestamp(),
                'dateUpdated' => $block->dateUpdated?->getTimestamp(),
                'fields'      => $block->getSerializedFieldValues(),
            ]);
    }
}
