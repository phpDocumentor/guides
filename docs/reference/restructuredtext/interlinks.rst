..  include:: /include.rst.txt

=========
Interlinks
=========

Interlinks let you reference documentation in other projects.
The feature is inspired by Sphinx intersphinx links: guides reads inventory data
for configured external projects and resolves references by inventory id.

Configure external inventories
==============================

Define one or more inventories in ``guides.xml``:

..  code-block:: xml
    :caption: guides.xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <guides>
        <inventory id="t3coreapi" url="https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/"/>
    </guides>

The ``id`` is the interlink domain used in references.

Use interlinks in reStructuredText
==================================

Interlinks are used in reference-oriented text roles by prefixing the target with
``<inventory-id>:``.

Examples with ``:ref:``
-----------------------

..  code-block::

    :ref:`t3coreapi:assets`
    :ref:`Working with assets <t3coreapi:assets>`

Examples with ``:doc:``
-----------------------

..  code-block::

    :doc:`t3coreapi:ApiOverview/Assets/Index`
    :doc:`Assets chapter <t3coreapi:ApiOverview/Assets/Index>`

Resolution behavior
===================

- The resolver first selects a repository that reports the requested inventory id.
- It then resolves the target in that inventory.
- If no repository provides the inventory id, a warning is logged and the link is not resolved.

See also
========

- :ref:`Text Roles <basic-text-role>`
- :ref:`custom_interlink_resolvers`

