=======
Caching
=======

The guides library supports caching to improve performance when rendering
documentation repeatedly. This is particularly useful for development workflows
and CI/CD pipelines where the same documentation is rendered multiple times.

Inventory Caching
=================

When using intersphinx-style cross-references between documentation projects,
the guides library fetches inventory files (``objects.inv.json``) from remote URLs.
These HTTP requests can be cached to avoid repeated network fetches.

The ``JsonLoader`` accepts any `PSR-16 (Simple Cache)`_ implementation. When no
cache is provided, an in-memory ``ArrayAdapter`` is used, which deduplicates
requests within a single process.

.. _PSR-16 (Simple Cache): https://www.php-fig.org/psr/psr-16/

Usage
-----

Default (in-memory deduplication only):

.. code-block:: php

    use phpDocumentor\Guides\ReferenceResolvers\Interlink\JsonLoader;
    use Symfony\Component\HttpClient\HttpClient;

    $jsonLoader = new JsonLoader(HttpClient::create());

With a persistent cache:

.. code-block:: php

    $jsonLoader = new JsonLoader($httpClient, $cache);

``$cache`` is any ``Psr\SimpleCache\CacheInterface``. Implementations are
available on Packagist; see the
`PSR-16 providers list <https://packagist.org/providers/psr/simple-cache-implementation>`_.
Configure the TTL on the cache adapter itself.

What to expect
--------------

- On a cache hit, the parsed inventory array is returned directly; no HTTP
  request is made.
- On a miss, the inventory is fetched over HTTP and stored via
  ``CacheInterface::set()``; the adapter's own default lifetime applies.
- Cache keys are derived from the URL with an ``xxh128`` hash and a
  ``guides_inventory_`` prefix, so they are stable across processes.
