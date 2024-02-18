=========
Directive
=========

Directives are the extension points of ReStructuredText. They are used to add custom nodes to the document tree.
The parser will do the basic parsing of a directive. Then it will hand over the directive to a directive handler, which
will do the actual processing of the directive.

.. hint::

   This project contains a lot of directives. You can find them in the :php:namespace:`\phpDocumentor\Guides\RestructuredText\Directives` namespace,
   including the way to use them.

To implement a directive you need to create a class that extends :php:class:`\phpDocumentor\Guides\RestructuredText\Directives\BaseDirective`,
and register it with the parser using a :ref:`custom extension <developer-extension>`.

.. code-block:: php
    :caption: your-extension.php

    <?php

    return static function (ContainerConfigurator $container): void {
        $container->services()
            ->set(YourDirective::class)
            ->tag('phpdoc.guides.directive');

By design, this library distinguishes between three types of directives:

- :php:class:`phpDocumentor\Guides\RestructuredText\Directives\SubDirective`
  This is the most common directive type. It is used to add a new node type to the document tree that allows you to do
  custom rendering. See :ref:`directive-reference` for examples.

- :php:class:`phpDocumentor\Guides\RestructuredText\Directives\ActionDirective`
  Action directives are not producing nodes in the document tree. They can be used to perform actions on the document.
  For example set the default language for code blocks or configure other settings.

- :php:class:`phpDocumentor\Guides\RestructuredText\Directives\BaseDirective`,
  more or less a basic directive handler.
  This is the most advanced directive type. You are on your own here. You need to do everything yourself.

Implement a sub directive
=========================

A sub directivehandler is a node with child nodes. The parser will take care of the parsing of the directive content.
All you need to do is create a node and add the content.

..  literalinclude:: directive/subdirective.php
    :language: php
    :caption: your-extension/Directive/ExampleDirective.php
    :lineos:



