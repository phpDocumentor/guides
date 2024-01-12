============
Architecture
============

The project is build around the core library that is called ``guides``. This library
contains all core components of the project. Like the different layers :doc:`parser`,
:doc:`compiler` and :doc:`render` and includes the basic Nodes and templates to create
output.

Installation of the core library can be done using ``composer``::

.. code:: bash
        composer require phpdocumentor/guides

The other components are using the core library and extend it with additional
functionality. For example the ``guides-markdown`` component adds support for
Markdown documents and the ``guides-restructuredtext`` component adds support for
ReStructuredText documents.

All components are designed to be open for extension so you can bring your own parser,
template engine or other component. The core library is designed to be the glue between
all components.

The ``guides``, ``guides-markdown`` and ``guides-restructuredtext`` are seen as the main
libaries of the project. The other components are optional and can be used to extend the
functionality of the main libraries for specific use cases.

Application flow
================

Processing documents is done in a few steps.

#. :php:class:`Parsing <\phpDocumentor\Guides\Parser>` The first step is to parse the document. This is done by the :doc:`parser` component. The
   parser component will parse the document and create a tree of nodes. Each node
   represents a part of the document. For example a paragraph, a list or a table.
#. :php:class:`Compiling <\phpDocumentor\Guides\Compiler\Compiler>` The second step is to compile the tree of nodes. This is done by the :doc:`compiler`
   component. During the compilation modifications can be made to the tree of nodes. For
   example the compiler can add a table of contents to the tree of nodes.

#. :php:class:`Rendering <\phpDocumentor\Guides\Renderer\BaseTypeRenderer>` The third step is to render the tree of nodes. This is done by the :doc:`render`
   component. The render component will render the tree of nodes to a specific output
   format. By default twig templates are used to render nodes to HTML. But you can
   create your own templates to render nodes to other formats. Or implement your own
   renderer to use a different template engine.

.. uml:: _uml/application-flow.puml
    :caption: Application flow
