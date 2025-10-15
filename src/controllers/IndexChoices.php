<?php

namespace boldminded\dexter\controllers;

use boldminded\dexter\services\Config;
use boldminded\dexter\services\indexer\CategoryGroup;
use boldminded\dexter\services\indexer\FileVolume;
use boldminded\dexter\services\indexer\Section;
use boldminded\dexter\services\indexer\UserGroup;

class IndexChoices
{
    public static function get(): array
    {
        $config = new Config();
        $indexTypes = [
            Section::getPrefix() => $config->get('indices.entries'),
            FileVolume::getPrefix() => $config->get('indices.files'),
            CategoryGroup::getPrefix() => $config->get('indices.categories'),
            UserGroup::getPrefix() => $config->get('indices.users'),
        ];

        $sections = Section::getAll();
        $fileSystems = FileVolume::getAll();
        $categoryGroups = CategoryGroup::getAll();
        $userGroups = UserGroup::getAll();

        $choices = [
            'Sections' => [],
            'File Systems' => [],
            'Category Groups' => [],
            'Users' => [],
        ];

        foreach ($indexTypes as $type => $indices) {
            foreach ($indices as $sourceName => $indexName) {
                if (isset($sections[$sourceName])) {
                    $choices['Sections'][$type . $sections[$sourceName]['handle']] = sprintf('%s (%s)', $sections[$sourceName]['name'], $indexName);
                }
                if (isset($fileSystems[$sourceName])) {
                    $choices['File Systems'][$type . $fileSystems[$sourceName]['handle']] = sprintf('%s (%s)', $fileSystems[$sourceName]['name'], $indexName);
                }
                if (isset($categoryGroups[$sourceName])) {
                    $choices['Category Groups'][$type . $categoryGroups[$sourceName]['handle']] = sprintf('%s (%s)', $categoryGroups[$sourceName]['name'], $indexName);
                }
                if (isset($userGroups[$sourceName])) {
                    $choices['Users'][$type . $userGroups[$sourceName]['handle']] = sprintf('%s (%s)', $userGroups[$sourceName]['name'], $indexName);
                }
            }
        }

        return $choices;
    }

    public static function asOptionGroups(): array
    {
        $choices = self::get();
        $optionGroups = [
            [
                'label' => 'Choose',
                'value' => '',
            ]
        ];

        foreach ($choices as $groupLabel => $options) {
            $optionGroup[] = [
                'optgroup' => $groupLabel,
            ];

            foreach ($options as $value => $label) {
                $optionGroup[] = [
                    'label' => $label,
                    'value' => $value,
                ];
            }

            $optionGroups = array_merge($optionGroups, $optionGroup);
            $optionGroup = [];
        }

        return $optionGroups;
    }
}
