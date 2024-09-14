.. _extending_compiler:

=========
Compiler
=========

To extend the compiler you need a custom extension. This guide assumes you have a basic understanding of the compiler
and have read the :ref:`compiler-component` description. If you haven't read that yet, please do so before continuing.
Also, this guide assumes you have an :ref:`extension <developer-extension>` set up.

If you want to extend the compiler to support new features there are two options:

- Implement a :php:interface:`phpDocumentor\Guides\Compiler\NodeTransformer`
- Implement a :php:interface:`phpDocumentor\Guides\Compiler\CompilerPass`

NodeTransformer
===============

Node transformers are used to transform specific types of nodes in the AST. This is useful when you want to remove
a node type or manipulate nodes of a specific type. Your new node transformer should implement the :php:interface:`phpDocumentor\Guides\Compiler\NodeTransformer`
interface and you should register it in the dependency injection container.

.. code-block:: php
    :caption: your-extension.php

    <?php

    return static function (ContainerConfigurator $container): void {
        $container->services()
            ->set(YourNodeTransformer::class)
            ->tag('phpdoc.guides.compiler.nodeTransformers');

.. note::

    The tag `phpdoc.guides.compiler.nodeTransformers` is used to register the node transformer in the compiler. The higher
    the priority of the node transformer, the earlier it will be executed. Where highest priority is `PHP_INT_MAX`, lower
    number is lower priority.

CompilerPass
============

If you want to do more complex transformations, for example transformations that require multiple nodes to be transformed
you should implement a :php:interface:`phpDocumentor\Guides\Compiler\CompilerPass`. A compiler pass needs to be registered
just like a node transformer.

.. code-block:: php
    :caption: your-extension.php

    <?php

    return static function (ContainerConfigurator $container): void {
        $container->services()
            ->set(YourCompilerPass::class)
            ->tag('phpdoc.guides.compiler.passes');

.. note::

    The tag `phpdoc.guides.compiler.passes` is used to register the node transformer in the compiler. The higher
    the priority of the node transformer, the earlier it will be executed. Where highest priority is `PHP_INT_MAX`, lower
    number is lower priority.
