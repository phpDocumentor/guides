============
About Guides
============

..  attention::
    **EXPERIMENTAL**: This library is still under heavy development, and should be considered a work in progress project.
    It is subject to change or removal without notice, including without consideration for backward compatibility.

phpDocumentor's Guides library takes hand-written documentation in code repositories, creates an AST from that and feeds
it to a renderer to create the desired output.

As part of this goal, the Guides library itself is more of a framework where you can plug in support for an input 
format, such as Restructured Text, and plug in an output format to output towards, such as HTML.

Supported Formats
=================

Input
-----

As this is a new component, the number of formats are limited and can expand in the future

-   RestructuredText; Well-supported, though still work in progress
-   Markdown; Early stages, not working yet

Output
------

As this is a new component, the number of formats are limited and can expand in the future

-   HTML
-   LaTeX; Well-supported, though still work in progress

Usage
=====

See :doc:`usage`.
