Index Feature Showcase
=======================

This project exercises every `.. index::` entry type across multiple pages,
demonstrating that genindex aggregates entries project-wide rather than
per-page.

.. toctree::

   page-one
   page-two
   page-three

.. index:: ! main entry demo

Main Entry
----------

A ``! entry`` line marks the entry as the "main" definition, rendered with
the ``main-entry`` class. Defined on the root page to show main-entry
detection isn't tied to any particular page.
