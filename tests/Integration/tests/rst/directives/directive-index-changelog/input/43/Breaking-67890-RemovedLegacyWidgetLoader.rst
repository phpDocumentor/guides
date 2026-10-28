=========================================
Breaking: #67890 - Removed WidgetLoader
=========================================

What Was Removed
=================

The legacy ``WidgetLoader`` class has been removed in favor of the new
Widget API.

Consequences
============

Any code still calling ``WidgetLoader::load()`` will fail with a fatal
error.

Affected Installations
=======================

Installations with extensions that use the old hook-based widget mechanism.

.. index:: Backend, NotScanned, ext:core
