..  include:: /include.rst.txt

:project:
    Guides
:version:
    dev-main

====================
|project|
====================

Description
    |composer_description|
Source
   |composer_support_source|
Report issues
   |composer_support_issues|
Latest public documentation
    |composer_support_docs|

If you are building your own application you can install the libraries using `Composer <https://getcomposer.org/>`__::

.. code:: bash

    composer require phpdocumentor/guides

This will install all basic libraries needed to get started to get started.
All libraries come with support for `Symfony dependency injection <https://symfony.com/doc/current/components/dependency_injection.html>`__.
This will help you to get started with the libraries in symfony applications.

Read more about writing your own application in the :doc:`developers` section.

.. tip::

    The following 3 steps let you render the documentation that you are currently reading using the framework you
    are currently reading about::

        git clone git@github.com:phpDocumentor/guides.git .
        composer install
        vendor/bin/guides

    You will then find the rendered documentation in the directory output.

.. toctree::
    :hidden:

    usage
    configuration
    extension/index
    rst-reference/index
    about
