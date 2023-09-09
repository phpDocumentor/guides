..  include:: /include.rst.txt

=============
Configuration
=============

This library can be configured in two ways:

1.  ``guides.xml`` in the current directory. This file configures the Guides
    library (which extensions to load) and can configure default values for
    the project specific settings.
2.  ``guides.xml`` in the parent of the vendor directory. Options are
    overridden by the first location

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
