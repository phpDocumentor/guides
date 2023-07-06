..  include:: /include.rst.txt

=============
Configuration
=============

Global configuration
====================

Settings that you want to have regardless of the documentation you are
building should live in ``guides.xml`` in the current working directory.


Per-manual configuration
========================

If you need different settings for different manuals you are building,
you can do so by creating a ``settings.php`` file in the input directory
of the manual you are building (that is the directory you would specify
as a first argument to the CLI).

That file needs to return a ``ProjectSettings``, and typically looks as
follows:

.. code-block:: php

   <?php

   use phpDocumentor\Guides\Settings\ProjectSettings;

   return new ProjectSettings(
       title: 'My Documentation',
       version:'42.12.7'
   );
