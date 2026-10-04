Changelog
=========

Simulates a TYPO3-style Changelog with per-version directories, demonstrating
the scoped ``.. genindex::`` directive: two scoped listings (one per version)
and one unscoped listing, all on the same page.

.. toctree::

   Changelog/12.3/Feature-100-OldThing
   Changelog/12.4/Feature-200-NewThing
   Changelog/12.4/Breaking-300-RemovedThing

Index For 12.4
--------------

.. genindex::
   :scope: Changelog/12.4/

Index For 12.3
--------------

.. genindex::
   :scope: Changelog/12.3/

Full Index
----------

.. genindex::
