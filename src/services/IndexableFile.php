<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use craft\elements\Asset as AssetElement;
use BoldMinded\DexterCore\Contracts\ConfigInterface;
use BoldMinded\DexterCore\Service\HostReachability;
use BoldMinded\DexterCore\Contracts\CustomFieldInterface;
use BoldMinded\DexterCore\Contracts\IndexableFileInterface;
use BoldMinded\DexterCore\Contracts\IndexableInterface;

class IndexableFile implements IndexableInterface, IndexableFileInterface
{

    public function __construct(
        private AssetElement $file,
        private ?ConfigInterface $config = null
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
            'uid' => $this->file->getCanonicalUid(),
            'title' => $this->file->title,
            'slug' => $this->file->slug,
            'dateCreated' => $this->file->dateCreated,
            'dateUpdated' => $this->file->dateUpdated,
            'enabled' => $this->file->enabled,
            'siteId' => $this->file->siteId,
            'status' => $this->file->getStatus(),
            'url' => $this->url(),
        ], $this->file->getFieldValues());
    }

    /**
     * The asset URL in the form named by the assetUrls setting. Falls back to
     * Craft's own URL when no config was supplied, which keeps the older
     * two-argument-less construction working.
     */
    private function url(): string
    {
        if ($this->config === null) {
            return (string) ($this->file->getUrl() ?? '');
        }

        return AssetUrlResolver::resolve($this->file, $this->config);
    }

    public function getId(): int|string
    {
        return $this->file->getCanonicalUid();
    }

    public function getUniqueId(): string
    {
        return 'file_' . $this->file->siteId . '_' . $this->file->getCanonicalUid();
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

    /**
     * The address an AI provider is given for this file.
     *
     * Image description works by handing the provider a URL that it fetches
     * itself, which fails for any site it cannot reach from the internet --
     * local development, staging behind auth, an intranet. In those cases the
     * image is inlined as a data URI instead, so describing works without the
     * site being publicly addressable.
     *
     * Only images are inlined. Documents are parsed locally and only their
     * extracted text is sent, so their URL is never fetched remotely.
     */
    public function getAbsoluteUrl(): string
    {
        $url = (string) $this->file->getUrl();

        if (!$this->isImage() || HostReachability::isPublic($url, $this->config)) {
            return $url;
        }

        return $this->dataUri() ?: $url;
    }

    /**
     * The image as a data URI, or an empty string when it cannot be read.
     *
     * Guarded by a size limit: providers cap request bodies, and base64
     * inflates by about a third, so a large original would be rejected after
     * the cost of reading and encoding it.
     */
    private function dataUri(): string
    {
        $path = $this->getAbsolutePath();

        if ($path === '' || !is_readable($path)) {
            return '';
        }

        $maxBytes = (int) ($this->config?->get('maxInlineImageBytes') ?: 15 * 1024 * 1024);
        $size = @filesize($path);

        if ($size === false || $size > $maxBytes) {
            return '';
        }

        $contents = @file_get_contents($path);

        if ($contents === false) {
            return '';
        }

        return 'data:' . ($this->getMimeType() ?: 'image/jpeg')
            . ';base64,' . base64_encode($contents);
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
