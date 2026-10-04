Changelog
=========

Simulates the TYPO3 Core Changelog convention: one directory per version,
each containing one page per change. Every change page ends with a single,
type-less, comma-separated ``.. index::`` line right after the last section,
with no heading following it. Each version directory's own overview page
carries a ``.. genindex::`` scoped to that directory, alongside the
project-wide ``:template: genindex`` page for everything combined.

.. toctree::

   43/index
   42/index
