<?php

declare(strict_types=1);

namespace boldminded\dexter\services\pipeline;

use craft\elements\Category;
use Litzinger\DexterCore\Contracts\ConfigInterface;
use Litzinger\DexterCore\Contracts\IndexableInterface;

class CategoryGroupPipeline
{
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

        $categoryGroups = $this->config->get('categoryGroups');
        $categoryMenuGroups = $this->config->get('categoryMenuGroups');

        if (empty($categoryGroups)) {
            return $values;
        }

        $categoryFields = $this->indexable->getRelated('craft\fields\Categories');
        $collection = [];


        /** @var Category $category */
        foreach ($categoryFields as $categoryField) {
            $categories = $this->indexable->getEntity()->getFieldValue($categoryField->handle)->all();

            foreach ($categories as $category) {
                if (!in_array($category->getGroup()->getHandle(), $categoryMenuGroups)) {
                    continue;
                }

                $collection[$category->getGroup()->name][] = $category->title;
            }
        }

        if (!empty($collection)) {
            $values['categoryGroups'] = $collection;
        }

        return $values;
    }
}
