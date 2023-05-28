..  include:: /include.rst.txt

=====
Usage
=====

Standalone Usage
================

#.  Clone the repository::

        git clone git@github.com:phpDocumentor/guides.git

#.  Install with composer::

        composer install

#.  Put the files to be rendered into a directory i.e. `my-docs-input`

#.  Run the application to render your docs::

        vendor/bin/guides my-docs-input my-docs-output --theme=bootstrap

..  hint::
    For more options run::

        vendor/bin/guides -h

Integrated Usage
================

To use this library within your project you can require it via composer::

    composer req phpdocumentor/guides-cli

You can find an example of such an integration here:
`TYPO3 Example RST Rendering with phpDocumentor/guides <https://github.com/TYPO3-Documentation/rst-rendering-demo>`__.
