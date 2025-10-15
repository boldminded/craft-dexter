<?php

declare(strict_types=1);

namespace boldminded\dexter\services\elements;

use boldminded\dexter\services\Filterable;
use craft\base\Element;
use craft\elements\Category as CategoryElement;
use BoldMinded\DexterCore\Contracts\ConfigInterface;
use BoldMinded\DexterCore\Contracts\IndexableInterface;

class Category implements ElementInterface
{
    use Filterable;

    public bool $returnsMultipleValues = false;

    public function __construct(
        protected ConfigInterface $config
    ) {
    }

    public function getClassName(): string
    {
        return 'craft\elements\Category';
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
        $categories = $entry->getFieldValue($fieldHandle)->all();

        $indexableProperties = $this->config->get('categoryIndexableProperties');

        // If default/simple configuration, just return titles as strings
        if (is_array($indexableProperties) &&
            count($indexableProperties) === 1 &&
            $indexableProperties[0] === 'title'
        ) {
            return array_map(
                fn (CategoryElement $category) => $category->title,
                $categories
            );
        }

        return array_map(
            fn (CategoryElement $category) => $this->toArray($category),
            $categories
        );
    }

    private function toArray(CategoryElement $category): array
    {
        return array_merge($category->toArray([
                'id',
                'uid',
                'siteId',
                'groupId',
                'title',
                'slug',
                'uri',
                'root',
                'lft',
                'rgt',
                'level'
            ]), [
                'dateCreated' => $category->dateCreated?->getTimestamp(),
                'dateUpdated' => $category->dateUpdated?->getTimestamp(),
                'fields'      => $category->getSerializedFieldValues(),
            ]);
    }
}
