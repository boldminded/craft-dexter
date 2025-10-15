<?php

declare(strict_types=1);

namespace boldminded\dexter\services\elements;

use boldminded\dexter\services\Filterable;
use craft\base\Element;
use craft\elements\Address as AddressElement;
use Litzinger\DexterCore\Contracts\ConfigInterface;
use Litzinger\DexterCore\Contracts\IndexableInterface;

class Address implements ElementInterface
{
    use Filterable;

    public bool $returnsMultipleValues = false;

    public function __construct(
        protected ConfigInterface $config
    ) {
    }

    public function getClassName(): string
    {
        return 'craft\elements\Address';
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
        $addresses = $entry->getFieldValue($fieldHandle)->all();

        return array_map(
            fn (AddressElement $address) => $this->toArray($address),
            $addresses
        );
    }

    private function toArray(AddressElement $address): array
    {
        return array_merge($address->toArray([
                'id',
                'uid',
                'siteId',
                'title',
                'addressLine1',
                'addressLine2',
                'locality',
                'administrativeArea',
                'postalCode',
                'countryCode'
            ]), [
                'dateCreated' => $address->dateCreated?->getTimestamp(),
                'dateUpdated' => $address->dateUpdated?->getTimestamp(),
            ]);
    }
}
