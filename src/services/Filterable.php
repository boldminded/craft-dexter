<?php

declare(strict_types=1);

namespace boldminded\dexter\services;

use Adbar\Dot;

trait Filterable
{
    protected function filterValues(
        array $indexableFields = [],
        array $values = [],
    ): array
    {
        $defaultIndexableFields = array_keys($values);

        if (!empty($indexableFields)) {
            $defaultIndexableFields = $indexableFields;
        }

        $dot = new Dot($values);

        $values = array_filter($dot->flatten(), function ($path) use ($defaultIndexableFields, $values): bool {
            foreach ($defaultIndexableFields as $allowedPath) {
                $regex = $this->allowedPathToRegex($allowedPath, $values[$allowedPath] ?? '');

                if (preg_match($regex, $path)) {
                    return true;
                }
            }

            return false;
        }, ARRAY_FILTER_USE_KEY);

        return (new Dot())->set($values)->all();
    }

    private function allowedPathToRegex(string $allowedPath, mixed $pathValue): string
    {
        // If the value is an array and the path doesn't contain a dot, assume it's a single-field array.
        if (!str_contains($allowedPath, '.') && is_array($pathValue)) {
            $allowedPath = $allowedPath . '.*';
        }

        // Trailing ".*" means "this path and everything below it"
        if (str_ends_with($allowedPath, '.*')) {
            $prefix = preg_quote(substr($allowedPath, 0, -2), '/');
            // Allow "fields.someField" itself, or "fields.someField.something"
            return '/^' . $prefix . '(\..+)?$/';
        }

        // Treat "*" as a single-segment wildcard
        $pattern = preg_quote($allowedPath, '/');
        $pattern = str_replace('\*', '[^.]+', $pattern);

        return '/^' . $pattern . '$/';
    }
}
