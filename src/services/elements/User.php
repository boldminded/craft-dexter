<?php

declare(strict_types=1);

namespace boldminded\dexter\services\elements;

use boldminded\dexter\services\Filterable;
use craft\base\Element;
use craft\elements\User as UserElement;
use BoldMinded\DexterCore\Contracts\ConfigInterface;
use BoldMinded\DexterCore\Contracts\IndexableInterface;

class User implements ElementInterface
{
    use Filterable;

    public bool $returnsMultipleValues = false;

    public function __construct(
        protected ConfigInterface $config
    ) {
    }

    public function getClassName(): string
    {
        return 'craft\elements\User';
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
        $users = $entry->getFieldValue($fieldHandle)->all();

        return array_map(
            fn (UserElement $user) => $this->toArray($user),
            $users
        );
    }

    private function toArray(UserElement $user): array
    {
        return array_merge($user->toArray([
                'id',
                'uid',
                'siteId',
                'username',
                'email',
                'firstName',
                'lastName',
                'fullName',
            ]), [
                'dateCreated' => $user->dateCreated?->getTimestamp(),
                'dateUpdated' => $user->dateUpdated?->getTimestamp(),
                'fields'      => $user->getSerializedFieldValues(),
            ]);
    }
}
