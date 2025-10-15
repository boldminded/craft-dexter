<?php

declare(strict_types=1);

namespace boldminded\dexter\services\elements;

use boldminded\dexter\services\Filterable;
use craft\base\Element;
use craft\elements\Tag as TagElement;
use Litzinger\DexterCore\Contracts\ConfigInterface;
use Litzinger\DexterCore\Contracts\IndexableInterface;

class Tag implements ElementInterface
{
    use Filterable;

    public bool $returnsMultipleValues = false;

    public function __construct(
        protected ConfigInterface $config
    ) {
    }

    public function getClassName(): string
    {
        return 'craft\elements\Tag';
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
        $tags = $entry->getFieldValue($fieldHandle)->all();

        $indexableProperties = $this->config->get('tagIndexableProperties');

        // If default/simple configuration, just return titles as strings
        if (count($indexableProperties) === 1 && $indexableProperties[0] === 'title') {
            return array_map(
                fn (TagElement $category) => $category->title,
                $tags
            );
        }

        return array_map(
            fn (TagElement $tag) => $this->toArray($tag),
            $tags
        );
    }

    private function toArray(TagElement $tag): array
    {
        return array_merge($tag->toArray([
                'id',
                'uid',
                'siteId',
                'groupId',
                'title',
                'slug',
            ]), [
                'dateCreated' => $tag->dateCreated?->getTimestamp(),
                'dateUpdated' => $tag->dateUpdated?->getTimestamp(),
                'fields'      => $tag->getSerializedFieldValues(),
            ]);
    }
}
