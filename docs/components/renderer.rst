..  include:: /include.rst.txt

.. _renderer-component:

========
Renderer
========

A Renderer transforms the :abbreviation:`AST (abstract syntax tree)` into a
desired output format.

The most common renderers handle each document separately. For example the
:php:class:`\phpDocumentor\Guides\Renderer\HtmlRenderer` renders the AST into
HTML.

Each renderer must implement
:php:interface:`\phpDocumentor\Guides\Renderer\TypeRenderer`.

Basic document
type renderers like the HTML or Latex renderer can extend
the :php:class:`\phpDocumentor\Guides\Renderer\BaseTypeRenderer`. The
:php:`BaseTypeRenderer` creates a
:php:class:`\phpDocumentor\Guides\Handlers\RenderDocumentCommand` for each
document in the document tree. The :php:`RenderDocumentCommand` passes the
rendering to the NodeRenders which do the actual rendering.

All renderers must be registered in the ContainerConfigurator of the extension
with the tag :php:`'phpdoc.renderer.typerenderer'` and additional format information.

Example: a plaintext renderer
=============================

Create a class called :php:`PlaintextRenderer` which just extends
:php:class:`\phpDocumentor\Guides\Renderer\BaseTypeRenderer`.

..  literalinclude:: _renderer/_PlaintextRenderer.php
    :language: php
    :caption: src/Renderer/PlaintextRenderer.php

Register the new renderer in the container:

..  literalinclude:: _renderer/_myextension.php
    :language: php
    :caption: resources/config/myextension.php

You can now configure your project to also generate output in plaintext:

..  code-block:: php
    :caption: guides.xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <guides xmlns="https://www.phpdoc.org/guides"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="https://www.phpdoc.org/guides packages/guides-cli/resources/schema/guides.xsd"
    >
        <extension class="MyVendor\MyExtension"/>
        <output-format>txt</output-format>
    </guides>

You can now define :ref:`custom templates <extending_templates>` or even custom
NodeRenderer for the new output format.

..  todo: document NodeRenderer and then link them from here.
