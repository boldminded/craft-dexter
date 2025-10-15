<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use craft\i18n\I18N;
use BoldMinded\DexterCore\Contracts\TranslatorInterface;

class TranslatorFactory
{
    public static function create(I18N $translator): TranslatorInterface
    {
        return new Translator($translator);
    }
}
