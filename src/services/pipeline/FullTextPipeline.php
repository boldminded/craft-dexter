<?php

declare(strict_types=1);

namespace boldminded\dexter\services\pipeline;

use BoldMinded\DexterCore\Contracts\ConfigInterface;
use BoldMinded\DexterCore\Contracts\IndexableInterface;
use BoldMinded\DexterCore\Service\StopWordRemover;

class FullTextPipeline
{
    private StopWordRemover $stopWordRemover;

    public function __construct(
        private IndexableInterface $indexable,
        private ConfigInterface $config
    ) {
        $this->stopWordRemover = new StopWordRemover();
    }

    public function __invoke(array $values): array
    {
        if (empty($values)) {
            return [];
        }

        if ($this->config->get('includeFullText') === false) {
            return $values;
        }

        $values['__full_text'] = $this->flatten($values);

        return $values;
    }

    private function flatten(array $array): string
    {
        $flat = [];

        array_walk_recursive($array, function ($value, $key) use (&$flat) {
            if (
                !in_array($key, ['id', 'uid'], true)
                && $value
                && is_scalar($value)
                && !is_numeric($value)
            ) {
                $flat[] = (string) $value;
            }
        });

        $text = strip_tags(implode(' ', array_unique($flat)));

        // @todo trigger an event/hook to modify on the fly?
        $text = $this->stopWordRemover->remove($text, $this->config->get('stopWords'));

        return $text;
    }
}
