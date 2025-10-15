<?php

namespace boldminded\dexter\controllers;

use Craft;
use FilesystemIterator;
use RecursiveDirectoryIterator;

class DirectoryReader
{
    public static function read(string $path): array
    {
        if (!is_readable($path)) {
            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterError',
                    Craft::t('dexter', $path . ' is not readable or does not exist.')
                );

            return [];
        }

        $foundFiles = iterator_to_array(
            new RecursiveDirectoryIterator($path,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS),
        );

        ksort($foundFiles);
        $files = [];

        foreach ($foundFiles as $file) {
            $files[$file->getPathname()] = $file->getFilename();
        }

        return $files;
    }
}
