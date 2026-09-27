Example Documentation Page
==========================

This page demonstrates several Sphinx/reStructuredText index entries.

.. index:: single: installation

Installation
------------

This section has a simple index entry for ``installation``.

To install the package, run:

.. code-block:: console

   pip install example-package


.. index::
   single: configuration
   pair: configuration; file
   pair: configuration; environment variables

Configuration
-------------

This section creates multiple index entries. In the generated index, readers
could find this section under "configuration", "configuration; file", and
"configuration; environment variables".

Configuration can be loaded from a file or from environment variables.


.. index::
   pair: API; authentication
   pair: authentication; token
   single: access token
   see: token; access token

Authentication
--------------

The API uses access tokens for authentication.

An access token identifies the client application and authorizes API requests.


.. index::
   triple: CLI; command; deploy
   pair: deployment; command
   single: deploy command

Deploying from the CLI
----------------------

Use the ``deploy`` command to publish the application:

.. code-block:: console

   example-cli deploy --environment production


.. index::
   single: performance; optimization
   pair: cache; invalidation
   seealso: optimization; caching

Performance
-----------

Caching can improve performance, but cache invalidation must be handled
carefully.


.. index:: ! main entry example

Main Index Entry
----------------

The exclamation mark marks this as a main index entry in Sphinx.

This is useful when several pages mention a concept, but one page is the
primary explanation of that concept.


.. index::
   module: example_package
   single: Python module; example_package

Python Module
-------------

This section indexes a Python module.

The package exposes the ``example_package`` module.


.. index::
   single: troubleshooting
   pair: error handling; logging
   pair: logging; diagnostics

Troubleshooting
---------------

When debugging problems, enable diagnostic logging and inspect error messages.

Useful troubleshooting steps include:

* checking configuration values
* verifying authentication tokens
* reviewing log output


Glossary
--------

.. glossary::

   access token
      A credential used to authenticate API requests.

   configuration file
      A file containing settings used by the application.

   cache invalidation
      The process of removing or refreshing stale cached data.