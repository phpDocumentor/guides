..  include:: /include.rst.txt

.. _custom_interlink_resolvers:

===================
Interlink Resolvers
===================

Interlinks are external references resolved from inventory files.
The format is inspired by Sphinx intersphinx inventories and lets guides resolve
links like ``project-id:target`` against a configured inventory source.

Implement a custom resolver
===========================

Create a class implementing
:php:class:`phpDocumentor\Guides\ReferenceResolvers\Interlink\InventoryLinkResolver`.

The key method is ``resolveInventoryLink()``. It receives the parsed cross-reference
node and should return a :php:class:`phpDocumentor\Guides\ReferenceResolvers\Interlink\ResolvedInventoryLink`
when the target can be resolved, otherwise ``null``.

Register your resolver in DI
============================

Register your service with the tag ``phpdoc.guides.interlink_resolver``.
The chained resolver collects all services with this tag.

..  code-block:: php
    :caption: your-extension/resources/config/your-extension.php

    <?php

    declare(strict_types=1);

    use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
    use YourVendor\YourExtension\Interlink\MyInventoryResolver;

    return static function (ContainerConfigurator $container): void {
        $container->services()
            ->defaults()
            ->autowire()
            ->autoconfigure()
            ->set(MyInventoryResolver::class)
            ->tag('phpdoc.guides.interlink_resolver');
    };

Your resolver is then considered together with the built-in default repository.

Disable the default repository
==============================

If your extension should fully control interlink resolution, disable the built-in
``DefaultInventoryRepository`` by setting the parameter
``phpdoc.guides.interlink.default_repository.enabled`` to ``false``.

..  code-block:: php
    :caption: your-extension/resources/config/your-extension.php

    <?php

    declare(strict_types=1);

    use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

    return static function (ContainerConfigurator $container): void {
        $container->parameters()
            ->set('phpdoc.guides.interlink.default_repository.enabled', false);
    };

When disabled, the default repository reports no available inventories, so only
custom tagged resolvers participate in interlink resolution.
