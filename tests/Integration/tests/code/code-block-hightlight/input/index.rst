Title
=====

The default language is empty::

    Some plain text

Also in explicit code-blocks without language:

..  code-block::

    Some plain text

..  highlight:: javascript

Now the default language is JavaScript::

    var language = 'JavaScript';

Also in explicit code-blocks without language:

..  code-block::

    var language = 'JavaScript';

However explicit languages take precedence

..  code-block:: xml

    <language>XML</language>
