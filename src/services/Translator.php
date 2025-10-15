<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use craft\i18n\I18N;
use BoldMinded\DexterCore\Contracts\TranslatorInterface;

class Translator implements TranslatorInterface
{
    public function __construct(private I18N $translator)
    {
    }

    public function get(string $key): string
    {
        return $this->translator->translate('dexter', $key, [], 'en-US');
    }
}
