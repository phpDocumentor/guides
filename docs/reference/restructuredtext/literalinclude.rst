..  include:: /include.rst.txt

..  _literalinclude:

==============
Literalinclude
==============

The ``literalinclude`` directive includes the content of another file as a code block. It lets the documentation
show a real, working file instead of a copy that has to be kept in sync by hand.

..  code-block::

    ..  literalinclude:: Example.php
        :language: php

The path is resolved relative to the document that contains the directive, a path starting with ``/`` relative to
the root of the documentation. If the file cannot be read, an error is logged and nothing is rendered in its place.
Rendering with ``--fail-on-error`` turns that error into a failing build.

Including only a part of a file
===============================

Often only one region of a file is worth showing. The options ``:start-after:`` and ``:end-before:`` select that
region by the text of the lines enclosing it:

..  code-block::

    ..  literalinclude:: Example.php
        :language: php
        :start-after: // begin example
        :end-before: // end example

The region starts on the line *following* the first line that contains the ``start-after`` text, and ends on the
line *preceding* the first line that contains the ``end-before`` text. Both marker lines are left out. The
``end-before`` text is searched behind the start of the region, so the same marker may occur several times in one
file.

Either option can be used on its own: ``start-after`` alone includes everything down to the end of the file,
``end-before`` alone everything from the beginning of the file.

Markers remain correct when the file is edited above or below the selected region. That is why they are the better
choice for a file that is under active development.

If no line contains the given text, a warning is logged and nothing is included, so that the option cannot silently
publish the very content it was meant to exclude. The same happens when the marked region turns out to be empty, or
when the ``end-before`` text occurs only above the line matched by ``start-after``. Rendering with ``--fail-on-log``
turns any of these warnings into a failing build.

Options
=======

``:language:``
    Language used for syntax highlighting, for example ``php``, ``bash`` or ``yaml``.

``:caption:``
    Caption rendered above the code block. Inline markup such as ``**bold**`` is allowed.

``:start-after:``
    Text of the line the included region starts after. The line itself is not included.

``:end-before:``
    Text of the line the included region ends before. The line itself is not included.

``:emphasize-lines:``
    Line numbers to be highlighted, for example ``3,5-6``. Counted within the included region.

``:linenos:``
    Displays line numbers, starting at 1.

``:lineno-start:``
    First line number to display. Also switches the numbering on.

``:number-lines:``
    Displays line numbers. Takes the first line number as an optional value.
