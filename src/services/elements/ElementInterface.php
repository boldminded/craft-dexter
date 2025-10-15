<?php

declare(strict_types=1);

namespace boldminded\dexter\services\elements;

use Litzinger\DexterCore\Contracts\IndexableInterface;

interface ElementInterface
{
    public function getClassName(): string;

    public function getValue(string $fieldHandle, IndexableInterface $indexable): mixed;
}
