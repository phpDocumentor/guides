..  include:: /include.rst.txt

=============
Configuration
=============

The cli tool is able to read configuration from a ``guides.xml`` file.
You should put this file in the current working directory where you execute
the tool. Generally speaking, this is the root of your project.

Using a configuration file allows you to set project-specific settings and
keep them under version control. Not all options available in the configuration
are available on the command line. Such as the :ref:`extension configuration`.

.. note::

    The ``guides.xml`` file is not required. If it is not present, the tool
    will use the default configuration.

Global configuration
====================

Settings that you want to have regardless of the documentation you are
building should live in ``guides.xml`` in the current working directory.

.. literalinclude:: ./basic-config.xml
   :language: xml

See the ``guides.xsd`` file for all available config options.

Extension configuration
=======================

Some extensions allow extra configuration to be added to the ``guides.xml``

.. literalinclude:: ./extension-config.xml
   :language: xml

.. hint::

   If you want to learn more about the extensions, see the :doc:`/developers/extensions/index` documentation.
