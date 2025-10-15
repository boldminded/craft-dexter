<?php

namespace boldminded\dexter\services\indexer;

use BoldMinded\DexterCore\Service\Indexer\ReIndexCommands;

class ReIndexCommandsFactory
{
    private string $indexName = '';

    public function create(string $indexSource): ReIndexCommands
    {
        $sourceType = $this->determineSourceType($indexSource);
        $sourceId = $this->getSourceId($indexSource, $sourceType);

        if ($sourceType === null) {
            throw new \InvalidArgumentException("Invalid index source: $indexSource");
        }

        $this->indexName = $sourceId;

        return match ($sourceType) {
            'entries' => new ReIndexCommandsSection($sourceId),
            'files' => new ReIndexCommandsFileVolume($sourceId),
            'categories' => new ReIndexCommandsCategoryGroup($sourceId),
            'users' => new ReIndexCommandsUserGroup($sourceId),
        };
    }

    public function getIndexName(): string
    {
        return $this->indexName;
    }

    private function determineSourceType(string $indexSource): string|null
    {
        if (strpos($indexSource, Section::getPrefix()) === 0) {
            return 'entries';
        } elseif (strpos($indexSource, FileVolume::getPrefix()) === 0) {
            return 'files';
        } elseif (strpos($indexSource, CategoryGroup::getPrefix()) === 0) {
            return 'categories';
        } elseif (strpos($indexSource, UserGroup::getPrefix()) === 0) {
            return 'users';
        } else {
            return null;
        }
    }

    private function getSourceId(string $indexSource, string $sourceType): string|null
    {
        if ($sourceType === 'entries') {
            return str_replace(Section::getPrefix(), '', $indexSource);
        } elseif ($sourceType === 'files') {
            return str_replace(FileVolume::getPrefix(), '', $indexSource);
        } elseif ($sourceType === 'categories') {
            return str_replace(CategoryGroup::getPrefix(), '', $indexSource);
        } elseif ($sourceType === 'users') {
            return str_replace(UserGroup::getPrefix(), '', $indexSource);
        } else {
            return null;
        }
    }
}
