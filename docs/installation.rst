============
Installation
============

This project can be used in two ways:

* :ref:`Standalone commandline tool <cli>`
* :ref:`Library in your own application <library>`

Standalone (recommended)
=====================================

.. _cli:

The commandline tool allows you start rendering your documentation without having to install any other software if
you have PHP installed.

To use the commandline tool you need to install it using `Composer <https://getcomposer.org/>`__::

.. code:: bash

    composer require --dev phpdocumentor/guides-cli

This will install the commandline tool in the vendor/bin directory. You can then use it as follows::

.. code:: bash

    vendor/bin/guides ./docs

The commandline tool is build for extension, if you do not have special needs this is the
recommended way to get started. You can learn more about how to extend the commandline tool in the :doc:`/cli/index` section.

Library (advanced)
===============================

.. _library:

If you are building your own application you can install the libraries using `Composer <https://getcomposer.org/>`__::

.. code:: bash

    composer require phpdocumentor/guides

This will install all basic libraries needed to get started to get started.
All libraries come with support for `Symfony dependency injection <https://symfony.com/doc/current/components/dependency_injection.html>`__.
This will help you to get started with the libraries in symfony applications.

Read more about writing your own application in the :doc:`/developers/index` section.
