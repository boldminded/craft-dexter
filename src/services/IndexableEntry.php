<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use craft\elements\Entry;
use BoldMinded\DexterCore\Contracts\CustomFieldInterface;
use BoldMinded\DexterCore\Contracts\IndexableInterface;

class IndexableEntry implements IndexableInterface
{
    public function __construct(
        private Entry $entry
    ){
    }

    public function getScope(): string
    {
        return 'entries';
    }

    public function getTypes(): array
    {
        return [$this->entry?->type?->handle ?? ''];
    }

    public function getEntity(): Entry
    {
        return $this->entry;
    }

    public function get(string $key): mixed
    {
        return $this->entry->getProperty($key);
    }

    public function getValues(): array
    {
        return array_merge([
            'id' => $this->entry->id,
            'uid' => $this->entry->uid,
            'title' => $this->entry->title,
            'slug' => $this->entry->slug,
            'postDate' => $this->entry->postDate,
            'expiryDate' => $this->entry->expiryDate,
            'dateCreated' => $this->entry->dateCreated,
            'dateUpdated' => $this->entry->dateUpdated,
            'enabled' => $this->entry->enabled,
            'archived' => $this->entry->archived,
            'siteId' => $this->entry->siteId,
            'sectionId' => $this->entry->sectionId,
            'typeId' => $this->entry->typeId,
            'status' => $this->entry->getStatus(),
        ], $this->entry->getFieldValues());
    }

    public function getId(): int|string
    {
        return $this->entry->getCanonicalUid();
    }

    public function getUniqueId(): string
    {
        return 'entry_' . $this->entry->entry_id;
    }

    public function getRelated(string $type): array
    {
        $fieldLayout = $this->entry->getFieldLayout();

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
