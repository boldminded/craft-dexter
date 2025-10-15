<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use craft\elements\Asset as AssetElement;
use Litzinger\DexterCore\Contracts\CustomFieldInterface;
use Litzinger\DexterCore\Contracts\IndexableFileInterface;
use Litzinger\DexterCore\Contracts\IndexableInterface;

class IndexableFile implements IndexableInterface, IndexableFileInterface
{

    public function __construct(
        private AssetElement $file
    ) {
    }

    public function getScope(): string
    {
        return 'files';
    }

    public function getTypes(): array
    {
        return [$this->file?->volume?->handle ?? ''];
    }

    public function getEntity(): AssetElement
    {
        return $this->file;
    }

    public function get(string $key): mixed
    {
        return $this->file->getProperty($key);
    }

    public function getValues(): array
    {
        return array_merge([
            'id' => $this->file->id,
            'uid' => $this->file->uid,
            'title' => $this->file->title,
            'slug' => $this->file->slug,
            'dateCreated' => $this->file->dateCreated,
            'dateUpdated' => $this->file->dateUpdated,
            'enabled' => $this->file->enabled,
            'siteId' => $this->file->siteId,
            'status' => $this->file->getStatus(),
            'url' => $this->file->getUrl(),
        ], $this->file->getFieldValues());
    }

    public function getId(): int|string
    {
        return $this->file->uid;
    }

    public function getUniqueId(): string
    {
        return 'file_' . $this->file->uid;
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

    public function getMimeType(): string
    {
        return $this->file->mimeType;
    }

    public function getAbsoluteUrl(): string
    {
        return $this->file->getUrl();
    }

    public function getAbsolutePath(): string
    {
        return $this->file->getPath();
    }

    public function isImage(): bool
    {
        return $this->file->kind === 'image';
    }
}
