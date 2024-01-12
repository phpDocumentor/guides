..  include:: /include.rst.txt

:version:
    dev-main

====================
|project|
====================

Description
    |composer_description|
Source
   |composer_support_source|
Report issues
   |composer_support_issues|
Latest public documentation
    |composer_support_docs|

This project contains a framework for rendering documentation. It provides a simple commandline tool to render
your documentation from `reStructuredText Markup <https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html>`__ and
`Markdown <https://daringfireball.net/projects/markdown/>`__. to HTML or LaTeX. And can be extended to support other
formats.

Besides the commandline tool it also provides a number of libraries that can be used to build your own application
to render the supported formats. To any format you want. On these pages is explained how to use the commandline tool
and how to use the libraries.

If you are looking for a complete solution to create a documentation website then you may want to look at
`PHPDocumentor <https://phpdoc.org/>`__.

.. tip::

    The following 3 steps let you render the documentation that you are currently reading using the framework you
    are currently reading about::

        git clone git@github.com:phpDocumentor/guides.git .
        composer install
        vendor/bin/guides

    You will then find the rendered documentation in the directory output.

.. toctree::
    :hidden:

    installation
    cli/index
    developers/index
    architecture
    reference/index
    contributions/index
    about
