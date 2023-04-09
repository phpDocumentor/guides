..  include:: /Includes.rst.txt
..  highlight:: html
..  index:: Templates; ViewHelper
..  _templates-viewhelpers:

==================
Custom ViewHelpers
==================

If your extension provides custom ViewHelpers you can describe their usage here.

If you want to use ViewHelpers from other extensions you need to add the namespace
declaration at the beginning of the template. The namespace declaration for the
example extension is::

    {namespace x=MyCompany\MyExtension\ViewHelpers}

Now you can use a ViewHelper of news with a code like::

    <x:myCustomViewHelper><!-- some comment --></x:myCustomViewHelper>

All ViewHelpers
================

exampleTag
----------

ViewHelper to do something


Examples
^^^^^^^^

Basic Example
"""""""""""""

Render the content of the VH as page title

Code: ::

    <x:exampleTag>{myRecord.title}</x:exampleTag>


Output: ::

    <xyz>TYPO3 is awesome</xyz>
