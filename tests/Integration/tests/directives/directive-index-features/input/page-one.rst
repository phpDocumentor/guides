Page One
========

.. index:: single: alpha

Single Entry
------------

A plain ``single: term`` entry: one top-level term with a direct link.

.. index:: single: beta; subbeta

Single Entry With Subterm
--------------------------

A ``single: term; subterm`` entry: a two-level entry (term > subterm), with
no reciprocal entry generated (unlike ``pair``).

.. index:: single: golf

See Target
----------

Defines "golf" as its own term, so a ``see`` entry on another page (see
page-three) can resolve and link across pages back to this one.

.. index:: single: widget

Widget Reference (Page One)
----------------------------

One of several occurrences of the same term ("widget") across different
pages, so its genindex entry accumulates multiple result links under one
``<dt>``.
