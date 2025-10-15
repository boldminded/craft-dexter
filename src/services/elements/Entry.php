<?php

declare(strict_types=1);

namespace boldminded\dexter\services\elements;

use craft\elements\Asset as AssetElement;
use craft\elements\Entry as EntryElement;
use craft\elements\User as UserElement;
use craft\elements\Category as CategoryElement;
use craft\fields\Entries as EntriesField;
use BoldMinded\DexterCore\Contracts\ConfigInterface;
use BoldMinded\DexterCore\Contracts\IndexableInterface;

class Entry implements ElementInterface
{
    public bool $returnsMultipleValues = false;

    public function __construct(
        private ConfigInterface $config
    ) {
    }

    public function getClassName(): string
    {
        return 'craft\elements\Entry';
    }

    public function getValue(
        string $fieldHandle,
        IndexableInterface $indexable
    ): mixed
    {
        return $this->serialize($indexable->getEntity(), $fieldHandle);
    }

    private function serialize(
        AssetElement|EntryElement|UserElement|CategoryElement|array $entity,
        string $fieldHandle,
        int $maxDepth = 2
    ): array
    {
        $entries = $entity->getFieldValue($fieldHandle)->all();

        return array_map(
            fn (EntryElement $child) => $this->toArray($child, $maxDepth),
            $entries
        );
    }

    /**
     * Recursively serialize an Entry (including nested Matrix/Entries fields).
     */
    private function toArray(
        EntryElement $entry,
        int $depth
    ): array
    {
        // @todo add config option to include values only, thus ignoring this meta data
        $values = [
            'id'          => $entry->id,
            'uid'         => $entry->uid,
            'siteId'      => $entry->siteId,
            'type'        => $entry->type->handle ?? null,
            'sortOrder'   => $entry->sortOrder,
            'dateCreated' => $entry->dateCreated?->getTimestamp(),
            'dateUpdated' => $entry->dateUpdated?->getTimestamp(),
            'title'       => $entry->title,
            'fields'      => $entry->getSerializedFieldValues(),
        ];

        if ($depth <= 0 || !$entry->getFieldLayout()) {
            return $values;
        }

        // Expand only Entries-type relations
        foreach ($entry->getFieldLayout()->getCustomFields() as $field) {
            if ($field instanceof EntriesField) {
                $children = $entry->getFieldValue($field->handle)->all();

                foreach ($children as $child) {
                    if ($child instanceof EntryElement) {
                        $values['fields'][$field->handle] = $this->toArray($child, $depth - 1);
                    }
                }

                //$values['fields'][$field->handle] = array_map(
                //    fn (Entry $child) => $this->toArray($child, $depth - 1),
                //    $children
                //);
            }
        }

        return $values;
    }
}
