<?php

declare(strict_types=1);

namespace boldminded\dexter\services\pipeline;

use boldminded\dexter\queue\UpdateFileJob;
use boldminded\dexter\services\FileUpdater;
use boldminded\dexter\services\LoggerFactory;
use boldminded\dexter\services\QueueFactory;
use Craft;
use craft\elements\Category;
use BoldMinded\DexterCore\Contracts\ConfigInterface;
use BoldMinded\DexterCore\Contracts\IndexableInterface;
use BoldMinded\DexterCore\Service\DocumentParsers\FileParserFactory;
use BoldMinded\DexterCore\Service\FileDescriber;

class FileDescribePipeline
{
    private FileDescriber $fileDescriber;

    public function __construct(
        private IndexableInterface $indexable,
        private ConfigInterface $config
    ) {
        $this->fileDescriber = new FileDescriber(
            config: $config,
            logger: LoggerFactory::create(Craft::getLogger()),
            fileParserFactory: new FileParserFactory($config)
        );
    }

    public function __invoke(array $values): array
    {
        if (empty($values)) {
            return [];
        }

        $whichOptions = $this->indexable->isImage() ? 'Image' : 'Document';
        $createAltText = $this->config->get(sprintf('parse%sContents.createAltText', $whichOptions)) === true;
        $createDescription = $this->config->get(sprintf('parse%sContents.createDescription', $whichOptions)) === true;
        $createCategories = $this->config->get(sprintf('parse%sContents.createCategories', $whichOptions)) === true;
        $replaceAltText = $this->config->get(sprintf('parse%sContents.replaceAltText', $whichOptions)) === true;
        $replaceDescription = $this->config->get(sprintf('parse%sContents.replaceDescription', $whichOptions)) === true;
        $replaceCategories = $this->config->get(sprintf('parse%sContents.replaceCategories', $whichOptions)) === true;

        if (
            !$createAltText
            && !$createCategories
            && !$createDescription
            && !$replaceCategories
            && !$replaceDescription
            && !$replaceAltText
        ) {
            return $values;
        }

        $description = $this->fileDescriber->describe(
            $this->indexable
        );

        if ($this->fileDescriber->isJson($description)) {
            $descriptionData = json_decode($description, true);
            $newAltText = $descriptionData['altText'] ?? ''; // @todo
            $newDescription = $descriptionData['description'] ?? '';
            $newCategories = $descriptionData['tags'] ?? [];
        } else {
            $newAltText = '';
            $newDescription = $description;
            $newCategories = [];
        }

        /** @var craft\elements\Asset $entity */
        $entity = $this->indexable->getEntity();

        $altTexFieldHandle = $this->config->get(sprintf('parse%sContents.altTextFieldHandle', $whichOptions)) ?: '';
        $descriptionFieldHandle = $this->config->get(sprintf('parse%sContents.descriptionFieldHandle', $whichOptions)) ?: '';
        $categoriesFieldHandle = $this->config->get(sprintf('parse%sContents.categoriesFieldHandle', $whichOptions)) ?: '';

        if ($descriptionFieldHandle && $replaceDescription && $newDescription) {
            $values[$descriptionFieldHandle] = $newDescription;
        }

        if ($altTexFieldHandle && $replaceAltText && $newAltText) {
            $values[$altTexFieldHandle] = $newAltText;
        }

        $existingCategories = array_map(
            fn($cat) => $cat->title,
            $entity->getFieldValue($categoriesFieldHandle)->all()
        );

        $values[$categoriesFieldHandle] = $existingCategories;

        asort($newCategories);
        asort($existingCategories);

        if ($categoriesFieldHandle && $replaceCategories && $newCategories !== $existingCategories) {
            $values[$categoriesFieldHandle] = $newCategories;
        }

        $useQueue = $this->config->get('useQueue') === true;

        if ($useQueue) {
            $queue = QueueFactory::create(Craft::$app->getQueue());
            $queue->push(UpdateFileJob::class, [
                'uid' => $this->indexable->getId(),
                'title' => $entity->title,
                'payload' => $values,
            ]);

            return $values;
        }

        (new FileUpdater($this->indexable, $this->config))->update([
            'uid' => $this->indexable->getId(),
            'title' => $entity->title,
            'payload' => $values,
        ]);

        return $values;
    }
}
