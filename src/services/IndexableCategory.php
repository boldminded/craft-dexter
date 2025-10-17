<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use craft\elements\Category;
use BoldMinded\DexterCore\Contracts\CustomFieldInterface;
use BoldMinded\DexterCore\Contracts\IndexableInterface;

class IndexableCategory implements IndexableInterface
{
    public function __construct(
        private Category $category
    ){
    }

    public function getScope(): string
    {
        return 'categories';
    }

    public function getTypes(): array
    {
        return [$this->category?->group?->handle ?? ''];
    }

    public function getEntity(): Category
    {
        return $this->category;
    }

    public function get(string $key): mixed
    {
        return $this->category->getProperty($key);
    }

    public function getValues(): array
    {
        return array_merge([
            'id' => $this->category->id,
            'uid' => $this->category->getCanonicalUid(),
            'title' => $this->category->title,
            'slug' => $this->category->slug,
            'dateCreated' => $this->category->dateCreated,
            'dateUpdated' => $this->category->dateUpdated,
            'enabled' => $this->category->enabled,
            'archived' => $this->category->archived,
            'siteId' => $this->category->siteId,
            'groupId' => $this->category->groupId,
            'status' => $this->category->getStatus(),
        ], $this->category->getFieldValues());
    }

    public function getId(): int|string
    {
        return $this->category->getCanonicalUid();
    }

    public function getUniqueId(): string
    {
        return 'category_' . $this->category->siteId . '_' . $this->category->getCanonicalUid();
    }

    public function getRelated(string $type): array
    {
        $fieldLayout = $this->category->getFieldLayout();

        return array_filter(array_map(static function ($field) use ($type) {
            return get_class($field) === $type ? $field : null;
        }, $fieldLayout->getCustomFields()));
    }

    /**
     * @return CustomFieldInterface[]
     */
    public function getCustomFields(): array
    {
        return [];
    }
}
