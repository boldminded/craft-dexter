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
        $this->options = $this->config->get(sprintf('parse%sContents', $whichOptions)) ?: [];
    }

    /**
     * @param  array{uid?: string, title?: string, payload?: array}  $values
     */
    public function update(array $values): bool
    {
        $file = Asset::find()
            ->uid($this->file->getId())
            ->one();

        if (!$file) {
            return false;
        }

        // Both callers -- the pipeline and UpdateFileJob -- wrap the generated
        // values in a `payload` key. Reading the outer array meant every
        // lookup missed and the fields were written as empty strings.
        $payload = $values['payload'] ?? $values;

        $update = false;

        $update = $this->applyText($file, 'descriptionFieldHandle', 'Description', $payload) || $update;
        $update = $this->applyText($file, 'altTextFieldHandle', 'AltText', $payload) || $update;
        $update = $this->applyCategories($file, $payload) || $update;

        if ($update === true) {
            Craft::$app->elements->saveElement($file, false, false);
        }

        return true;
    }

    /**
     * Write one generated string to its configured field.
     *
     * The create/replace distinction matters: create fills an empty field,
     * replace overwrites whatever is there. An author's own wording should
     * survive a re-index unless the site explicitly asked otherwise.
     */
    private function applyText(Asset $file, string $handleKey, string $flag, array $payload): bool
    {
        $handle = $this->options[$handleKey] ?? '';

        if ($handle === '') {
            return false;
        }

        $value = $payload[$handle] ?? null;

        if (!is_string($value) || trim($value) === '') {
            return false;
        }

        $existing = $this->read($file, $handle);
        $isEmpty = $existing === null || trim((string) $existing) === '';

        $create = ($this->options['create' . $flag] ?? false) === true;
        $replace = ($this->options['replace' . $flag] ?? false) === true;

        if (!($replace || ($create && $isEmpty))) {
            return false;
        }

        if ((string) $existing === $value) {
            return false;
        }

        $this->write($file, $handle, $value);

        return true;
    }

    /**
     * Relate generated keywords as categories, creating any that are missing.
     */
    private function applyCategories(Asset $file, array $payload): bool
    {
        $handle = $this->options['categoriesFieldHandle'] ?? '';
        $groupHandle = $this->options['categoryGroupHandle'] ?? '';

        if ($handle === '' || $groupHandle === '') {
            return false;
        }

        $categoryNames = $payload[$handle] ?? [];

        if (!is_array($categoryNames) || $categoryNames === []) {
            return false;
        }

        $create = ($this->options['createCategories'] ?? false) === true;
        $replace = ($this->options['replaceCategories'] ?? false) === true;

        if (!$create && !$replace) {
            return false;
        }

        $categoryIds = [];

        // Merge with what is already related unless replacing outright.
        if ($replace !== true) {
            $existing = $file->getFieldValue($handle);

            if ($existing && method_exists($existing, 'all')) {
                $categoryIds = array_map(fn ($cat) => $cat->id, $existing->all());
            }
        }

        foreach ($categoryNames as $categoryName) {
            $categoryId = (new CategoryUpdater($this->config))->create(
                (string) $categoryName,
                $groupHandle,
                $this->file->getEntity()->siteId
            );

            if ($categoryId) {
                $categoryIds[] = $categoryId;
            }
        }

        if ($categoryIds === []) {
            return false;
        }

        $file->setFieldValue($handle, array_values(array_unique($categoryIds)));

        return true;
    }

    /**
     * Read a value by handle, resolving dot notation into nested data.
     *
     * The config offers dot notation for nested fields, but getFieldValue()
     * treats a dotted string as a literal handle, so "seo.description" looked
     * for a field named exactly that rather than descending into a Matrix or
     * group. The descent is done here instead.
     */
    private function read(Asset $file, string $handle): mixed
    {
        if (!str_contains($handle, '.')) {
            return $file->getFieldValue($handle);
        }

        $segments = explode('.', $handle);
        $cursor = $file->getFieldValue(array_shift($segments));

        foreach ($segments as $segment) {
            if (is_array($cursor)) {
                $cursor = $cursor[$segment] ?? null;

                continue;
            }

            if (is_object($cursor) && isset($cursor->$segment)) {
                $cursor = $cursor->$segment;

                continue;
            }

            // Nothing further to descend into.
            return null;
        }

        return $cursor;
    }

    /**
     * Write a value by handle, building intermediate arrays for a dot path.
     *
     * A dotted handle passed straight to setFieldValue() would create a field
     * reference Craft never reads back, so the value would be silently lost.
     */
    private function write(Asset $file, string $handle, mixed $value): void
    {
        if (!str_contains($handle, '.')) {
            $file->setFieldValue($handle, $value);

            return;
        }

        $segments = explode('.', $handle);
        $root = array_shift($segments);

        $existing = $file->getFieldValue($root);
        $data = is_array($existing) ? $existing : [];

        $cursor = &$data;

        foreach ($segments as $segment) {
            // Anything already there that is not an array cannot be descended
            // into, so it is replaced rather than dropping the write.
            if (!isset($cursor[$segment]) || !is_array($cursor[$segment])) {
                $cursor[$segment] = [];
            }

            $cursor = &$cursor[$segment];
        }

        $cursor = $value;
        unset($cursor);

        $file->setFieldValue($root, $data);
    }
}
