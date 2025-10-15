<?php

declare(strict_types=1);

namespace boldminded\dexter\services\elements;

use boldminded\dexter\services\FilePipelines;
use boldminded\dexter\services\Filterable;
use boldminded\dexter\services\IndexableFile;
use craft\base\Element;
use craft\elements\Asset as AssetElement;
use League\Pipeline\PipelineBuilder;
use Litzinger\DexterCore\Contracts\ConfigInterface;
use Litzinger\DexterCore\Contracts\IndexableInterface;

class Asset implements ElementInterface
{
    public bool $returnsMultipleValues = true;

    public function __construct(
        protected ConfigInterface $config
    ) {
    }

    public function getClassName(): string
    {
        return 'craft\elements\Asset';
    }

    public function getValue(
        string $fieldHandle,
        IndexableInterface $indexable
    ): mixed
    {
        $value = $this->serialize($indexable->getEntity(), $fieldHandle);

        // Return the first asset's URL even if there are multiple assets.
        // Then return the full array of assets.
        return [
            $fieldHandle => $value[0]['url'] ?? '',
            $fieldHandle . '_meta' => $value,
        ];
    }

    private function serialize(Element $entry, string $fieldHandle): array
    {
        $assets = $entry->getFieldValue($fieldHandle)->all();

        return array_map(
            fn (AssetElement $asset) => $this->toArray($asset),
            $assets
        );
    }

    private function toArray(AssetElement $asset): array
    {
        $fileValues = array_merge($asset->toArray([
            'id',
            'uid',
            'siteId',
            'type',
            'url',
        ]), [
            'dateCreated' => $asset->dateCreated?->getTimestamp(),
            'dateUpdated' => $asset->dateUpdated?->getTimestamp(),
        ], $asset->getSerializedFieldValues());

        $values = $this->filePipeline($asset, $fileValues);

        return $values;
    }

    private function filePipeline(AssetElement $asset, array $values): array
    {
        $pipelineBuilder = new PipelineBuilder;
        $pipelines = FilePipelines::getPipelines($this->config);

        $pipelines = array_unique($pipelines);

        foreach ($pipelines as $pipelineClass) {
            $pipelineBuilder->add(
                new $pipelineClass(
                    new IndexableFile($asset),
                    $this->config
                )
            );
        }

        $pipelines = $pipelineBuilder->build();

        return $pipelines->process($values);
    }
}
