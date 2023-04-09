..  include:: /Includes.rst.txt
..  highlight:: typoscript
..  index::
    TSconfig
..  _configuration-tsconfig:

TSconfig reference
==================

If your extension uses TSconfig you can document it here.

This section covers all configurations which can be set with TSconfig.
Every configuration starts with :typoscript:`tx_myextension.`.

..  note::
    Just for clarification: TSconfig is in TYPO3 only used for configurations
    inside the backend!

..  index::
    TSconfig; Page
..  _configuration-tsconfig_page:

User TSconfig
=============

..  confval values will be added automatically to the index.

..  confval:: enableSomething

    :type: bool
    :Default: false

    If :typoscript:`true`, something is enabled...

    Example::

        tx_myextension.enableSomething = true


..  index::
   TSconfig; Page
..  _configuration-tsconfig_user:

Page TSconfig
=============

..  confval:: limitSomething

    :type: int
    :Default: 5

    This value limits something. If it is set to :typoscript:`0` the thing will
    be unlimited...


    Example, limit something to 10::

        tx_myextension.limitSomething = 10

..  confval:: someImportantPages

    :type: list
    :Default: ''

    Comma-list of fields from the pages-table. These fields are ...


    Example, limit something to 10::

        tx_myextension.someImportantPages = 4, 7, 42
