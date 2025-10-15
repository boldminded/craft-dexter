<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use Craft;
use craft\elements\Category;
use craft\helpers\StringHelper;
use Litzinger\DexterCore\Contracts\ConfigInterface;

class CategoryUpdater
{
    public function __construct(
        private ConfigInterface $config,
    ) {
    }

    public function create(
        string $categoryName,
        string $categoryGroupHandle,
        int $siteId = 1
    ): int {
        // Does this category already exist?
        $categoryName = trim($categoryName);

        // Resolve group
        $group = Craft::$app->categories->getGroupByHandle($categoryGroupHandle);

        if (!$group) {
            throw new \RuntimeException(sprintf(
                'Unknown category group: %s',
                $categoryGroupHandle
            ));
        }

        $slug = StringHelper::slugify($categoryName);

        // Try to find an existing category (by slug) in this group
        $existingCategory = Category::find()
            ->group($group->handle)
            ->siteId($siteId)
            ->slug($slug)
            ->one();

        if (!$existingCategory) {
            // Create it if missing
            $cat = new Category();
            $cat->groupId = $group->id;
            $cat->siteId = $siteId;
            $cat->title = $categoryName;

            if (!Craft::$app->elements->saveElement($cat)) {
                throw new \RuntimeException(sprintf(
                    'Failed to save category "%s": %s',
                    $categoryName,
                    json_encode($cat->getErrors())
                ));
            }

            return $cat->id;
        }

        // Category already exists, so return its id
        return $existingCategory->id;
    }
}
