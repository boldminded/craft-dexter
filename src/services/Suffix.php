<?php

namespace boldminded\dexter\services;

use Craft;
use craft\base\Element;

class Suffix
{
    public static function getByElement(Element $element): string
    {
        try {
            $handle = Craft::$app->getSites()->getSiteById($element->siteId)->handle;

            if ($handle !== 'default') {
                return '_' . $handle;
            }

            return '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    public static function getByUid(string $uid): string
    {
        try {
            $element = Craft::$app->getElements()->getElementByUid($uid);

            return self::getByElement($element);
        } catch (\Throwable $e) {
            return '';
        }
    }

    public static function getBySiteId(int $siteId): string
    {
        $handle = Craft::$app->getSites()->getSiteById($siteId)->handle;

        if ($handle !== 'default') {
            return '_' . $handle;
        }

        return '';
    }
}
