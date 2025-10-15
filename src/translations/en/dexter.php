<?php

return [
    'Dexter' => 'Dexter',
    'Dashboard' => 'Dashboard',
    'Import Settings' => 'Import Settings',
    'Export Settings' => 'Export Settings',
    'Clear Index' => 'Clear Index',
    'Delete Index' => 'Delete Index',
    'Re-Index' => 'Re-Index',

    '{indexName} has been cleared of all files.' => '{indexName} has been cleared of all files.',
    'Failed to clear {indexName} of all files: {errors}.' => 'Failed to clear {indexName} of all files: {errors}.',

    '{indexName} has been cleared of all entries.' => '{indexName} has been cleared of all entries.',
    'Failed to clear {indexName} of all entries: {errors}.' => 'Failed to clear {indexName} of all entries: {errors}.',

    '{indexName} has been cleared of all users.' => '{indexName} has been cleared of all users.',
    'Failed to clear {indexName} of all users: {errors}.' => 'Failed to clear {indexName} of all users: {errors}.',

    '{indexName} has been cleared of all categories.' => '{indexName} has been cleared of all categories.',
    'Failed to clear {indexName} of all categories: {errors}.' => 'Failed to clear {indexName} of all categories: {errors}.',

    'Could not make file: {path}. Be sure %s is a directory and is writable with 775 permissions.' => 'Could not make file: {path}. Be sure %s is a directory and is writable with 775 permissions.',
    'Could not write to file: {path}. Be sure it exists and is writable with 644 permissions.' => 'Could not write to file: {path}. Be sure it exists and is writable with 644 permissions.',
    'Created file: {path}' => 'Created file: {path}',

    'Error indexing entry: {msg}' => 'Error indexing entry: {msg}',
    'Error indexing category: {msg}' => 'Error indexing category: {msg}',
    'Error indexing file: {msg}' => 'Error indexing file: {msg}',
    'Error indexing user: {msg}' => 'Error indexing user: {msg}',

    'Indexing category: {title}' => 'Indexing category: {title}',
    'Indexing file: {title}' => 'Indexing file: {title}',
    'Updating file: {title}' => 'Updating file: {title}',
    'Indexing user: {title}' => 'Indexing user: {title}',
    'Indexing entry: {title}' => 'Indexing entry: {title}',

    'No index selected.' => 'No index selected.',
    'No settings file selected.' => 'No settings file selected.',

    'Invalid settings, could not complete export.' => 'Invalid settings, could not complete export.',
    'There was an error attempting to export settings from {provider}' => 'There was an error attempting to export settings from {provider}.',
    'Index exported successfully to {path}/{provider}/{indexName}.json' => 'Index exported successfully to {path}/{provider}/{indexName}.json',

    'Whoops! Looks like you need to choose an index to clear.' => 'Whoops! Looks like you need to choose an index to clear.',
    'Whoops! Looks like you need to choose an index to delete.' => 'Whoops! Looks like you need to choose an index to delete.',

    'Queued {totalObjects} objects for reindexing in <code>{indexName}</code>.' => 'Queued {totalObjects} objects for reindexing in <code>{indexName}</code>.',
    'Reindexed {totalObjects} objects in <code>{indexName}</code>.' => 'Reindexed {totalObjects} objects in <code>{indexName}</code>.',
    'There was an error attempting to reindex {indexName}: {errors}.' => 'There was an error attempting to reindex {indexName}: {errors}.',

    'Index <code>{indexName}</code> deleted successfully.' => 'Index <code>{indexName}</code> deleted successfully.',
    'Index <code>{indexName}</code> cleared successfully.' => 'Index <code>{indexName}</code> cleared successfully.',
    'There was an error attempting to import settings into <code>{indexName}</code>.' => 'There was an error attempting to import settings into <code>{indexName}</code>.',
    'Settings were successfully imported into <code>{indexName}</code>.' => 'Settings were successfully imported into <code>{indexName}</code>.',
];
