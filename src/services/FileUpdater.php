<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use Craft;
use craft\elements\Asset;
use BoldMinded\DexterCore\Contracts\ConfigInterface;
use BoldMinded\DexterCore\Contracts\IndexableInterface;

class FileUpdater
{
    private array $options;

    public function __construct(
        private IndexableInterface $file,
        private ConfigInterface $config,
    ) {
        $whichOptions = $this->file->isImage() ? 'Image' : 'Document';
        $this->options = $this->config->get(sprintf('parse%sContents', $whichOptions));
    }

    public function update(array $values): bool
    {
        $file = Asset::find()
            ->uid($this->file->getId())
            ->one();

        $descriptionFieldHandle = $this->options['descriptionFieldHandle'] ?? '';
        $categoriesFieldHandle = $this->options['categoriesFieldHandle'] ?? '';
        $createCategories = $this->options['createCategories'] === true;
        $replaceDescription = $this->options['replaceDescription'] === true;
        $replaceCategories = $this->options['replaceCategories'] === true;

        if ($file && $descriptionFieldHandle && $replaceDescription) {
            $file->setFieldValue(
                $descriptionFieldHandle,
                $values[$descriptionFieldHandle] ?? ''
            );

            Craft::$app->elements->saveElement($file);
        }

        $categoryNames = $values[$categoriesFieldHandle] ?? [];

        if (
            count($categoryNames) > 0
            && ($createCategories === true || $replaceCategories)
            && $this->options['categoryGroupHandle'] !== ''
        ) {
            $categoryIds = [];

            // If not replacing categories, we need to merge existing categories with new ones
            if ($replaceCategories !== true) {
                $categoryIds = array_map(
                    fn($cat) => $cat->id,
                    $file->getFieldValue($this->options['categoriesFieldHandle'])->all()
                );
            }

            foreach ($categoryNames as $categoryName) {
                $categoryIds[] = (new CategoryUpdater($this->config))->create(
                    $categoryName,
                    $this->options['categoryGroupHandle'],
                    $this->file->getEntity()->siteId
                );
            }

            if (count($categoryIds) > 0 && $file) {
                $file->categories = array_unique($categoryIds);
                Craft::$app->elements->saveElement($file);
            }
        }

        return true;
    }
}
