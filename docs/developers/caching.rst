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

The ``JsonLoader`` uses PSR-16 (Simple Cache) for all caching, defaulting to an
in-memory ``ArrayAdapter`` for request deduplication within a single process.

Basic Usage
-----------

**Default (in-memory caching):**

.. code-block:: php

    use phpDocumentor\Guides\ReferenceResolvers\Interlink\JsonLoader;
    use Symfony\Component\HttpClient\HttpClient;

    $httpClient = HttpClient::create();

    // Uses ArrayAdapter by default - deduplicates requests within same process
    $jsonLoader = new JsonLoader($httpClient);

**Persistent filesystem cache:**

.. code-block:: php

    use phpDocumentor\Guides\ReferenceResolvers\Interlink\JsonLoader;
    use Symfony\Component\Cache\Adapter\FilesystemAdapter;
    use Symfony\Component\Cache\Psr16Cache;
    use Symfony\Component\HttpClient\HttpClient;

    $httpClient = HttpClient::create();

    // Persistent cache across CLI invocations
    $pool = new FilesystemAdapter('inventory', 3600, '/path/to/cache');
    $cache = new Psr16Cache($pool);

    $jsonLoader = new JsonLoader($httpClient, $cache);

Cache Backends
--------------

You can use any PSR-16 compatible cache implementation:

**Filesystem Cache** (recommended for CLI tools):

.. code-block:: php

    use Symfony\Component\Cache\Adapter\FilesystemAdapter;
    use Symfony\Component\Cache\Psr16Cache;

    $pool = new FilesystemAdapter('inventory', 3600, '/path/to/cache');
    $cache = new Psr16Cache($pool);

**Redis Cache** (for shared/distributed caching):

.. code-block:: php

    use Symfony\Component\Cache\Adapter\RedisAdapter;
    use Symfony\Component\Cache\Psr16Cache;

    $redis = RedisAdapter::createConnection('redis://localhost');
    $pool = new RedisAdapter($redis, 'inventory');
    $cache = new Psr16Cache($pool);

Multi-Tier Caching
------------------

For optimal performance, use Symfony's ``ChainAdapter`` to combine fast in-memory
caching with persistent storage:

.. code-block:: php

    use Symfony\Component\Cache\Adapter\ArrayAdapter;
    use Symfony\Component\Cache\Adapter\ChainAdapter;
    use Symfony\Component\Cache\Adapter\FilesystemAdapter;
    use Symfony\Component\Cache\Psr16Cache;

    // L1: In-memory (fast) -> L2: Filesystem (persistent)
    $chain = new ChainAdapter([
        new ArrayAdapter(),                              // Check memory first
        new FilesystemAdapter('inventory', 3600, '/path/to/cache'),  // Fall back to disk
    ]);

    $cache = new Psr16Cache($chain);
    $jsonLoader = new JsonLoader($httpClient, $cache);

How it works:

- **Read**: Checks L1 (memory) first, then L2 (filesystem) on miss
- **Write**: Writes to all layers simultaneously
- **Benefit**: Fast in-memory hits for repeated references + persistence across requests

Cache Configuration
-------------------

``$cache``
    A PSR-16 ``CacheInterface`` implementation. When ``null``, defaults to an
    in-memory ``ArrayAdapter`` that deduplicates requests within the same process.
    Configure TTL when creating the cache adapter (e.g., ``FilesystemAdapter``'s
    second constructor argument).

Symfony Integration
-------------------

When using the guides library with Symfony's dependency injection:

.. code-block:: yaml

    # config/services.yaml
    services:
        # Multi-tier cache: memory + filesystem
        inventory.cache.chain:
            class: Symfony\Component\Cache\Adapter\ChainAdapter
            arguments:
                -
                    - !service { class: Symfony\Component\Cache\Adapter\ArrayAdapter }
                    - !service
                        class: Symfony\Component\Cache\Adapter\FilesystemAdapter
                        arguments:
                            $namespace: 'inventory'
                            $defaultLifetime: 3600
                            $directory: '%kernel.cache_dir%/guides'

        inventory.cache:
            class: Symfony\Component\Cache\Psr16Cache
            arguments:
                - '@inventory.cache.chain'

        phpDocumentor\Guides\ReferenceResolvers\Interlink\JsonLoader:
            arguments:
                $cache: '@inventory.cache'

Performance Impact
------------------

Inventory caching provides significant performance improvements when:

- Documentation references multiple external projects
- Building the same documentation repeatedly (CI/CD)
- External inventory files are large

For the TYPO3 documentation, inventory caching reduced render times by up to 53%
when referencing the PHP and TYPO3 core inventories.
