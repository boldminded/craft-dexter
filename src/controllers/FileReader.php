<?php

namespace boldminded\dexter\controllers;

use Craft;

class FileReader
{
    public static function read(string $file): bool|string
    {
        try {
            return file_get_contents($file);
        } catch (\Exception $exception) {
            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterError',
                    $exception->getMessage()
                );
        }

        return false;
    }

    public static function readJson(string $file): bool|array
    {
        try {
            $contents = file_get_contents($file);

            if (self::isJson($contents)) {
                return json_decode($contents, true);
            }

            return false;
        } catch (\Exception $exception) {
            Craft::$app
                ->getSession()
                ->setFlash(
                    'dexterError',
                    $exception->getMessage()
                );
        }

        return false;
    }

    public static function isJson($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
