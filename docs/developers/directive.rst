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

Reading directive options
=========================

A directive can declare the options it accepts using the repeatable
:php:class:`\phpDocumentor\Guides\RestructuredText\Directives\Attributes\Option` attribute on the directive class.
Each option has a ``name``, a ``type`` (one of the
:php:class:`\phpDocumentor\Guides\RestructuredText\Directives\OptionType` enum cases and ``String`` by default) and
an optional ``default`` value that is returned when the option is not present on the directive:

.. code-block:: php
    :caption: your-extension/Directive/ExampleDirective.php

    use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Option;
    use phpDocumentor\Guides\RestructuredText\Directives\OptionType;

    #[Option(name: 'title', description: 'The title of the node')]
    #[Option(name: 'count', type: OptionType::Integer, default: 3, description: 'How many entries to render')]
    #[Option(name: 'enabled', type: OptionType::Boolean, default: true)]
    final class ExampleDirective extends SubDirective
    {
        // ...
    }

Inside the directive you fetch a single option with
:php:method:`\phpDocumentor\Guides\RestructuredText\Directives\BaseDirective::readOption()`:

.. code-block:: php

    $title = $this->readOption($directive, 'title');   // string|null
    $count = $this->readOption($directive, 'count');   // int
    $enabled = $this->readOption($directive, 'enabled'); // bool

``readOption()`` returns a value typed according to the matching ``#[Option]`` attribute:

- an ``OptionType::String`` option returns ``string``, ``OptionType::Integer`` returns ``int``,
  ``OptionType::Boolean`` returns ``bool`` and ``OptionType::Array`` returns ``array``;
- when the option declares a ``default``, the default's type is added to the return type, so an
  option without a ``default`` returns ``<type>|null``;
- when no matching ``#[Option]`` attribute can be found for the given name, the return type is ``mixed``.

Static analysis
---------------

The return type of ``readOption()`` is inferred by a PHPStan
``DynamicMethodReturnTypeExtension`` shipped with the ``phpdocumentor/guides-restructured-text`` package. To
enable it, require the package and include its rule set in your ``phpstan.neon``:

.. code-block:: yaml
    :caption: phpstan.neon

    includes:
        - vendor/phpdocumentor/guides-restructured-text/rules.neon

With the rule enabled, PHPStan reports type mismatches when a directive uses an option in a way that
is incompatible with its declared ``#[Option]`` type (for example passing a possibly-``null`` ``string|null``
title to a method that requires a ``string``).

