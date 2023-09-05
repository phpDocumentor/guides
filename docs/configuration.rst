..  include:: /include.rst.txt

=============
Configuration
=============

This library can be configured in two ways:

1. ``guides.xml`` in the current directory. This file configures the Guides
   library (which extensions to load) and can configure default values for
   the project specific settings.
2. ``settings.php`` in the source files directory. This file can contain
   project/manual specific settings.

In most cases, you should do everything in the ``guides.xml`` file.
Documentations that compile the docs for a collection of projects might
want to use both config options. For instance, the ``guides.xml`` can
configure the documentation theme, whereas the ``settings.php`` configures
the title and version of each specific project.

Global configuration
====================

Settings that you want to have regardless of the documentation you are
building should live in ``guides.xml`` in the current working directory.

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <guides xmlns="https://www.phpdoc.org/guides"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="https://www.phpdoc.org/guides vendor/phpdocumentor/guides-cli/resources/schema/guides.xsd">

        <project title="phpDocumentor Guides"/>

        <extension class="phpDocumentor\Guides\Bootstrap"/>
    </guides>

The XML file can also be used to enable custom extensions. For instance, if
you want to use the Bootstrap HTML theme, use this configuration:

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <guides xmlns="https://www.phpdoc.org/guides"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="https://www.phpdoc.org/guides vendor/phpdocumentor/guides-cli/resources/schema/guides.xsd"

        html-theme="bootstrap"
    >
        <extension class="phpDocumentor\Guides\Bootstrap"/>
    </guides>

See the ``guides.xsd`` file for all available config options.

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
