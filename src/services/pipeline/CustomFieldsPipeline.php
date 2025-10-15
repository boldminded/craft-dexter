<?php

declare(strict_types=1);

namespace boldminded\dexter\services\pipeline;

use boldminded\dexter\services\elements\ElementTypeFactory;
use boldminded\dexter\services\Filterable;
use DateTimeInterface;
use Litzinger\DexterCore\Contracts\ConfigInterface;
use Litzinger\DexterCore\Contracts\IndexableInterface;
use Litzinger\DexterCore\Service\FieldValue;

class CustomFieldsPipeline
{
    use Filterable;

    public function __construct(
        private IndexableInterface $indexable,
        private ConfigInterface $config
    ) {
    }

    private function getIndexableFields(string $scope): array
    {
        $fields = $this->config->get('indexableFields.' . $scope);
        $types = $this->indexable->getTypes();

        foreach ($types as $type) {
            $fields = array_merge(
                $fields,
                $this->config->get('indexableFields.'. $scope . '[' . $type . ']') ?? [],
            );
        }

        return array_unique($fields);
    }

    public function __invoke(array $values): array
    {
        if (empty($values)) {
            return [];
        }

        $scope = $this->indexable->getScope();
        $indexableFields = $this->getIndexableFields($scope);
        $elementTypeFactory = new ElementTypeFactory($this->config);

        $newValues = [];

        foreach ($values as $fieldHandle => $value) {
            $elementType = $this->getElementType($value);

            if ($value instanceof DateTimeInterface) {
                $newValues[$fieldHandle] = $value->getTimestamp();
            } elseif ($elementType && $elementTypeFactory->isSupportedElement($elementType)) {
                $element = $elementTypeFactory->createElement($elementType);
                $elementValue = $element->getValue($fieldHandle, $this->indexable);

                if ($element->returnsMultipleValues) {
                    $newValues = array_merge($newValues, $elementValue);
                } else {
                    $newValues[$fieldHandle] = $elementValue;
                }
            } else {
                if ($this->hasGetValue($value)) {
                    $value = $value->getValue();
                }

                $newValues[$fieldHandle] = FieldValue::isJson($value) ? json_decode($value, true) : $value;
            }
        }

        if (!empty($indexableFields)) {
            $filteredValues = $this->filterValues($indexableFields, $newValues);

            return $filteredValues;
        }

        return array_merge($values, $newValues);
    }

    private function getElementType(mixed $element): string
    {
        if (isset($element->elementType) && is_string($element->elementType)) {
            return $element->elementType;
        }

        return is_object($element) && get_class($element) ? get_class($element) : '';
    }

    private function hasGetValue(mixed $element): bool
    {
        return is_object($element)
            && method_exists($element, 'getValue')
            && is_callable([$element, 'getValue']);
    }
}
