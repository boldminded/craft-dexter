<?php

namespace boldminded\dexter\controllers;

use Craft;

class FileWriter
{
    public static function write(string $file, string $data): bool
    {
        try {
            // Attempt to create the path to the cache file.
            $path = dirname($file);

            if (!@mkdir($path, 0775, true) && !is_dir($path)) {
                $message = Craft::t('dexter',
                    'Could not make file: {path}. Be sure %s is a directory and is writable with 775 permissions.', [
                        'path' => $path,
                    ]
                );

                Craft::error('[Dexter] ' . $message, __METHOD__);

                Craft::$app
                    ->getSession()
                    ->setFlash(
                        'dexterError',
                        $message
                    );

                return false;
            }

            if (file_put_contents($file, $data) === false) {
                @chmod($file, 0644);

                $message = Craft::t('dexter',
                    'Could not write to file: {path}. Be sure it exists and is writable with 644 permissions.', [
                        'path' => $path,
                    ]
                );

                Craft::error('[Dexter] ' . $message, __METHOD__);

                Craft::$app
                    ->getSession()
                    ->setFlash(
                        'dexterError',
                        $message
                    );

                return false;
            }

            $message = Craft::t('dexter',
                'Created file: {path}', [
                    'path' => $path,
                ]
            );

            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterNotice',
                    $message
                );

            return true;
        } catch (\Exception $exception) {
            Craft::error('[Dexter] ' . $exception->getMessage(), __METHOD__);

            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterError',
                    $exception->getMessage()
                );
        }

        return false;
    }
}
