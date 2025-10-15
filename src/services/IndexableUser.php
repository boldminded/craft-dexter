<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use craft\elements\User as UserElement;
use BoldMinded\DexterCore\Contracts\CustomFieldInterface;
use BoldMinded\DexterCore\Contracts\IndexableInterface;

class IndexableUser implements IndexableInterface
{

    public function __construct(
        private UserElement $user
    ) {
    }

    public function getScope(): string
    {
        return 'users';
    }

    public function getTypes(): array
    {
        return array_column($this->user?->groups ?? [], 'handle');
    }

    public function getEntity(): UserElement
    {
        return $this->user;
    }

    public function get(string $key): mixed
    {
        return $this->user->getProperty($key);
    }

    public function getValues(): array
    {
        return array_merge([
            'id' => $this->user->id,
            'uid' => $this->user->uid,
            'title' => $this->user->username,
            'username' => $this->user->username,
            'firstName' => $this->user->firstName,
            'lastName' => $this->user->lastName,
            'slug' => $this->user->slug,
            'dateCreated' => $this->user->dateCreated,
            'dateUpdated' => $this->user->dateUpdated,
            'enabled' => $this->user->enabled,
            'siteId' => $this->user->siteId,
            'status' => $this->user->getStatus(),
            'url' => $this->user->getUrl(),
        ], $this->user->getFieldValues());
    }

    public function getId(): int|string
    {
        return $this->user->uid;
    }

    public function getUniqueId(): string
    {
        return 'user_' . $this->user->uid;
    }

    public function getRelated(string $type): array
    {
        // Not applicable for files
        return [];
    }

    /**
     * @return CustomFieldInterface[]
     */
    public function getCustomFields(): array
    {
        // Not applicable for files
        return [];
    }
}
