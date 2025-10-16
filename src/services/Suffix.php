<?php

namespace boldminded\dexter\services;

use Craft;
use craft\base\Element;

class Suffix
{
    public static function get(Element|string $element): string
    {
        return self::getSite();

        try {
            if (is_string($element)) {
                $element = Craft::$app->getElements()->getElementByUid($element);
            }

            return '_' . Craft::$app->getSites()->getSiteById($element->siteId)->handle;
        } catch (\Throwable $e) {
            return '';
        }
    }

    public static function getSite(): ?string
    {
        try {
            $request = Craft::$app->getRequest();
            $siteHandle = $request->getQueryParam('site');

            if ($siteHandle && $siteHandle !== 'default') {
                return '_' . $siteHandle;
            }

            return '';

       } catch (\Throwable $e) {
            return null;
        }
    }
}
