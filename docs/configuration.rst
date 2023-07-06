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

That file needs to return an `array`, and typically looks as
follows:

..  code-block:: php

    <?php

    return [
        'title' => 'My Project',
        'version' => '3.1.4',
        'inventories' =>  ['t3coreapi' => 'https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/'],
    ];
