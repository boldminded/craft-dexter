<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use BoldMinded\DexterCore\Contracts\ConfigInterface;
use BoldMinded\DexterCore\Service\AssetUrl;
use craft\elements\Asset;

/**
 * Resolves an asset URL in the form named by the assetUrls setting.
 *
 * Craft's Asset::getUrl() returns whatever the volume's filesystem base URL
 * yields, which may already be relative or absolute depending on how the
 * filesystem is configured. The relative and path forms are derived from it
 * rather than rebuilt, so a filesystem configured with a relative base URL is
 * reported as-is instead of having a domain fabricated for it.
 */
class AssetUrlResolver
{
    public static function resolve(Asset $asset, ConfigInterface $config): string
    {
        $url = (string) ($asset->getUrl() ?? '');

        if ($url === '') {
            return (string) $asset->getFilename();
        }

        $path = parse_url($url, PHP_URL_PATH) ?: null;

        return AssetUrl::pick(
            AssetUrl::format($config),
            $path !== null ? basename($path) : (string) $asset->getFilename(),
            $path,
            $url,
        );
    }
}
