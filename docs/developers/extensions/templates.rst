..  include:: /include.rst.txt

..  _extending_templates:

===================
Extending Templates
===================

Register the templates overrides in your extension's
:ref:`Extension class <extension_symfony>`:

..  literalinclude:: _YourExtension.php
    :language: php
    :caption: your-extension/src/DependencyInjection/YourExtension.php
    :lineos:
    :emphasize-lines: 29-35

It is recommended to always extend an existing template so that the Twig files
of the base templates can be used as fallback for non-defined specific template
files.

In order to extend the default bootstrap theme, require the according base
extension in your extension's `composer.json`:

..  code-block:: json
    :caption: your-extension/composer.json

    {
      "name": "t3docs/typo3-docs-theme",
      "...": "...",
      "require": {
        "phpdocumentor/guides-theme-bootstrap": "dev-main"
      }
    }

And then set `'extends' => 'bootstrap'` (line 32 in the first code-snippet).

To extend the base template (plain HTML) require `phpdocumentor/guides` in your
`composer.json`, and omit the key `extends`:


..  code-block:: php
    :caption: your-extension/src/DependencyInjection/YourExtension.php

    $container->prependExtensionConfig('guides', [
        'themes' => ['mytheme' => dirname(__DIR__, 3) . '/resources/template'],
    ]);

You can now copy any Twig file from the extended extensions and change it with
the full power of `Twig <https://twig.symfony.com/>`__.

Have a look at the `custom theme for TYPO3 Documentation <https://github.com/TYPO3-Documentation/typo3-docs-theme>`__
for an example on how to create a theme which also features custom directives.
