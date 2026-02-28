..  include:: /include.rst.txt

===================
Parallel Processing
===================

The guides tool can render your documentation using multiple CPU cores,
significantly reducing build times for large documentation projects.

.. note::

    Parallel processing requires the ``pcntl`` PHP extension, which is only
    available on Linux and macOS. On Windows, the tool falls back to
    sequential processing automatically.

Automatic Detection
===================

By default, the guides tool automatically:

1. **Detects** available CPU cores on your system
2. **Enables** parallel processing when beneficial
3. **Falls back** to sequential when parallel isn't available

No configuration is required—parallel processing works out of the box.

When Parallel Processing Is Used
================================

The tool uses parallel processing when:

- The ``pcntl`` PHP extension is available
- Your documentation has 10 or more files
- Multiple CPU cores are detected

For small documentation sets (< 10 files), sequential processing is used
as the forking overhead isn't worth it.

Requirements
============

- **PHP Extension**: ``pcntl`` (included in most Linux/macOS PHP builds)
- **Operating System**: Linux or macOS (Windows not supported)
- **PHP Version**: 8.1 or higher

To check if pcntl is available:

.. code-block:: bash

    php -m | grep pcntl

Performance Benefits
====================

The performance gain depends on:

- **Number of CPU cores**: More cores = more parallel workers
- **Documentation size**: Larger projects benefit more
- **I/O speed**: SSD storage helps maximize throughput

Typical speedups:

- 4-core system: ~2-3x faster
- 8-core system: ~4-6x faster
- 16-core system: ~6-10x faster

Troubleshooting
===============

If parallel processing isn't working:

1. **Check pcntl extension**:

   .. code-block:: bash

       php -m | grep pcntl

   If not listed, install it or enable it in your php.ini.

2. **Check file count**: With fewer than 10 files, sequential is used.

3. **Check logs**: Enable verbose output to see processing mode:

   .. code-block:: bash

       ./vendor/bin/guides docs -v

For Developers
==============

For implementation details and integration into custom applications,
see :doc:`/developers/parallel-processing`.
