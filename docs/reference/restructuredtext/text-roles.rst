..  include:: /include.rst.txt

==========
Text Roles
==========

Text roles can be used to style content inline. Some text roles have advanced processing such as reference resolving.

You can also :doc:`add your own custom text roles </extension/text-roles>`.

Currently the following text roles are implemented:

.. phpdoc:class-list:: [?(@.interfacesIncludingInherited contains "\phpDocumentor\Guides\RestructuredText\TextRoles\TextRole")]

   .. phpdoc:name::
      :title: true
      :level: 2

   .. phpdoc:summary::
   .. phpdoc:description::


Examples
========

:ref:`Reference somewhere <basic-text-role>`

:doc:`Reference to document </extension/text-roles>`

:code:`Lorem Ipsum`

:abbreviation:`LIFO (last-in, first-out)`

:aspect:`Some important aspect`

:code:`result = (1 + x) * 32`

:command:`rm`

:dfn:`something`

:file:`/etc/passwd`

:guilabel:`&Cancel`

Press :kbd:`ctrl` + :kbd:`s`

:mailheader:`Content-Type`

:math:`A_\text{c} = (\pi/4) d^2`

:emphasis:`text`

:literal:`abc`

:strong:`text`, **text**

:subscript:`subscripted`, :sub:`sub`

:subscript:`superscript`, :sub:`sup`

:t:`Design Patterns`, :title:`Design Patterns`, :title-reference:`Design Patterns`
