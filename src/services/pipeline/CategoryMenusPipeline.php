<?php

declare(strict_types=1);

namespace boldminded\dexter\services\pipeline;

use craft\elements\Category;
use craft\fields\Categories;
use BoldMinded\DexterCore\Contracts\ConfigInterface;
use BoldMinded\DexterCore\Contracts\IndexableInterface;

class CategoryMenusPipeline
{
    private array $categoryLevels = [];
    private array $currentCategoryLevel = [];
    private array $groupCategories = [];

    public function __construct(
        private IndexableInterface $indexable,
        private ConfigInterface $config
    ) {
    }

    public function __invoke(array $values): array
    {
        if (empty($values)) {
            return [];
        }

        $entry = $this->indexable->getEntity();
        $indexableFields = $this->config->get('indexableFields.categories');

        $fieldLayout = $entry->getFieldLayout();

        foreach ($fieldLayout->getCustomFields() as $field) {
            if (
                $field instanceof Categories
                && in_array($field->handle, $indexableFields)
            ) {
                $values = $this->createCategoryMenu($field->handle, $values);
            }
        }

        return $values;
    }

    private function createCategoryMenu($fieldHandle, array $values): array
    {
        $categories = $this->indexable->getEntity()->getFieldValue($fieldHandle)->all();
        $group = $categories[0]?->getGroup();

        if (!$group) {
            return $values;
        }

        $groupCategories = Category::find()
            ->group($group->handle)
            ->all();

        /** @var Category $category */
        foreach ($groupCategories as $category) {
            $this->groupCategories[$category->id] = $category;
        }

        $categoryMenuGroups = $this->config->get('categoryMenuGroups');

        foreach ($categories as $category) {
            if (!in_array($category->getGroup()->getHandle(), $categoryMenuGroups)) {
                continue;
            }

            $this->renderTree($category);
        }

        foreach ($this->categoryLevels as $chain) {
            $ordered = array_reverse($chain);
            $count = count($ordered) - 1;

            if ($count <= 0) {
                $count = 0;
            }

            $values['categories.lvl' . $count][] = implode(' > ', $ordered);
            $values['categories.lvl' . $count] = array_unique($values['categories.lvl' . $count]);
        }

        return $values;
    }

    private function renderTree(Category $category)
    {
        $parentId = $category->getParentId() ?? 0;

        $this->currentCategoryLevel[] = $category->title;

        if ($parentId === 0) {
            // Save the current chain and reset the current
            $this->categoryLevels[] = $this->currentCategoryLevel;
            $this->currentCategoryLevel = [];

            return;
        }

        $this->renderTree($this->groupCategories[$parentId]);
    }
}
