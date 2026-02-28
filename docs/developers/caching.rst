=======
Caching
=======

The guides library supports optional caching to improve performance when rendering
documentation repeatedly. This is particularly useful for development workflows
and CI/CD pipelines where the same templates are rendered multiple times.

Template Caching
================

Twig templates can be compiled and cached to avoid re-parsing them on each render.
This significantly improves performance when rendering large documentation sets.

To enable template caching, pass a cache directory to the ``EnvironmentBuilder``:

.. code-block:: php

    use phpDocumentor\Guides\Twig\EnvironmentBuilder;
    use phpDocumentor\Guides\Twig\Theme\ThemeManager;

    $cacheDir = '/path/to/cache/twig';

    $environmentBuilder = new EnvironmentBuilder(
        themeManager: $themeManager,
        extensions: $extensions,
        cacheDir: $cacheDir,
    );

When caching is enabled:

- Compiled templates are stored in the specified directory
- Subsequent renders use the cached compiled templates
- Templates are automatically recompiled when the source changes (``auto_reload: true``)

To disable caching (default behavior), pass ``false`` or omit the parameter:

.. code-block:: php

    // Caching disabled (default)
    $environmentBuilder = new EnvironmentBuilder(
        themeManager: $themeManager,
        extensions: $extensions,
    );

Cache Directory Permissions
---------------------------

Ensure the cache directory:

- Exists and is writable by the PHP process
- Is excluded from version control (add to ``.gitignore``)
- Is cleared when deploying new template versions in production

Symfony Integration
-------------------

When using the guides library with Symfony's dependency injection, configure
the cache directory in your services configuration:

.. code-block:: yaml

    # config/services.yaml
    services:
        phpDocumentor\Guides\Twig\EnvironmentBuilder:
            arguments:
                $cacheDir: '%kernel.cache_dir%/guides/twig'

Performance Impact
------------------

Template caching provides the most benefit when:

- Rendering large documentation sets (100+ files)
- Running repeated builds during development
- Using complex templates with many includes

For small documentation sets or one-time builds, the overhead of cache management
may not provide significant benefits.
