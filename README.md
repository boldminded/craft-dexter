# Dexter

Advanced Algolia or Meilisearch index management and search

## Requirements

This plugin requires Craft CMS 5.8.0 or later, and PHP 8.2 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Dexter”. Then press “Install”.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require boldminded/craft-dexter

# tell Craft to install the plugin
./craft plugin/install dexter
```

# Documentation

https://docs.boldminded.com/dexter/docs-craft

## Search Endpoint

### GET
```
https://mysite.com/dexter/search?index=demo_collections&term=empire
```

### POST
```
https://mysite.com/dexter/search

body: {
    "index": "demo_collections",
    "term": "empire",
    "filters": {
        "limit": 6,
        "hybrid": {
          "embedder": "fullText"
        }
    }
}
```

## Template Tags

Iterate over search results object directly from Algolia or Meilisearch.

```twig
{% set results = craft.dexter.search({
    index: 'demo_collections',
    filter: [],
    perPage: 50,
}) %}

{% if results %}
    <ul>
        {% for result in results %}
            <li>{{ result.title }}</li>
        {% endfor %}
    </ul>
{% else %}
    <p>No results found.</p>
{% endif %}
```

Or, use the `idsOnly` parameter to retrieve only the UIDs of the search results. Then use the UIDs to fetch
the full entry object with Craft's entries tag.

```twig
{% set ids = craft.dexter.search({
    index: 'demo_collections',
    term: 'empire',
    filter: [],
    perPage: 50,
    idsOnly: true,
}) %}

{% set entries = craft.entries.section('collection').uid(ids).all() %}

{% if entries %}
    <ul>
        {% for result in entries %}
            <li>{{ result.title }}</li>
        {% endfor %}
    </ul>
{% else %}
    <p>No results found.</p>
{% endif %}
```
