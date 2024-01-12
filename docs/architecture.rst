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
