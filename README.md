# Dexter

Dexter is a powerful and highly configurable add-on that indexes your Craft entries, files, categories, and users in Algolia or
Meilisearch. It gives you full control over how your content is structured and searched.

Search implementations are rarely one-size-fits-all. Every site has unique business rules and content models. Dexter provides a reliable foundation for getting your content into a search index, along with the tools to tailor the output to your exact needs.

## Document full text indexing and search

Make all your files searchable with Dexter, including content from PDFs, Word, text files, CSVs, and XML. You can index all content, or limit it the number of pages or words.

## Generate image descriptions with AI

Add a connection to OpenAI through Dexter's configuration options and images will be described and categorized for searchability. Descriptions, alt text, and categories are saved back to the file object in Craft's database.

## Batch Re-Indexing

Dexter keeps your index in sync by updating it when entries are created, updated, or deleted. You can also run a full re-index to clear and rebuild the index from scratch. This feature works smoothly with the queue for background processing.

## Export and Import Settings

Dexter lets you export settings to a .json file in your config directory for version control, make modifications to it, and import back to the related index in Algolia or Meilisearch.

## Customizable Pipelines

Dexter supports all native fields out of the box. If you need more control, you can extend its behavior with PHP using the Pipeline pattern to transform your content however you like.

## Front-End Flexibility

Dexter is built for indexing and displaying the most relevant search results fast and efficiently. It includes tags for native template integration, and an API endpoint for creating a custom front-end search experience using JavaScript libraries like Algolia's InstantSearch or any other solution that fits your stack.

---

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
