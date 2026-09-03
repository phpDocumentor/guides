..  include:: /include.rst.txt

.. _term-index:

==========
Term index
==========

.. contents::

.. index:: reST directives; seealso

The ``index`` directive marks terms for a project-wide index, the way a
printed book's index points readers from a term to every page that discusses
it. Collected entries can then be rendered as a ``genindex`` page, either
project-wide or scoped to part of the project (e.g. one changelog version).

.. _index-directive:

The ``index`` directive
=======================

Collects one or more entries, project-wide, to be aggregated into a
``genindex`` page later. The directive itself is invisible in the rendered
page -- it produces no visible output where it's written.

Entry types
-----------

Each line of an ``index`` directive declares one entry. The prefix before the
first colon selects the entry type; without a recognized prefix, the whole
line is treated as a plain ``single`` term:

``single``
    A top-level term, optionally with a subterm:

    ..  code-block::

        .. index:: single: installation
        .. index:: single: installation; troubleshooting

``pair``
    Shorthand for two reciprocal ``single`` entries, so the term is findable
    either way round:

    ..  code-block::

        .. index:: pair: configuration; file

    This is equivalent to writing both
    ``single: configuration; file`` and ``single: file; configuration``.

``triple``
    Shorthand for three reciprocal entries covering every rotation of the
    given terms:

    ..  code-block::

        .. index:: triple: access; token; refresh

``module``
    Nests the given name under a literal top-level "module" term:

    ..  code-block::

        .. index:: module: Acme\Bundle\FooBundle

``see`` / ``seealso``
    A cross-reference from one term to another, rendered without its own
    link -- only as a pointer to the target term:

    ..  code-block::

        .. index:: see: token; access token
        .. index:: seealso: OAuth; access token

Prefixing an entry with ``!`` marks it as the "main" definition of that term,
which themes can render distinctly (e.g. bold) from its other occurrences:

..  code-block::

    .. index:: ! access token

Several entries can also be declared at once, one per line, under a single
directive:

..  code-block::

    .. index::
        single: configuration
        pair: configuration; file
        see: token; access token

Comma-separated, type-less form
-------------------------------

A line may also hold several comma-separated terms at once, each becoming
its own ``single`` entry -- the convention used by e.g. TYPO3 Core's
Changelog files:

..  code-block::

    .. index:: Backend, PHP-API, ext:core

.. _genindex-template:

The ``genindex`` template
=========================

The full, project-wide index is rendered by giving a document the
``template`` field, set to ``genindex``:

..  code-block::
    :caption: genindex.rst

    :orphan:
    :template: genindex

    Index
    =====

.. _genindex-directive:

The ``genindex`` directive
==========================

The ``.. genindex::`` directive renders the same kind of listing inline,
anywhere a document chooses to place it. Unlike the ``template`` field,
which produces at most one page for the *whole* project, ``genindex`` can
be placed anywhere and used more than once -- e.g. one listing per version
directory in a changelog:

..  code-block::

    Index for 12.4
    --------------

    .. genindex::
        :scope: Changelog/12.4/

    Full index
    ----------

    .. genindex::

``:scope:`` accepts a comma-separated list of path prefixes; entries from
documents whose path doesn't start with one of them are left out of that
particular listing. Omitting ``:scope:`` includes every entry in the
project.

Both the ``genindex`` template and the ``.. genindex::`` directive group
terms under an A-Z jumpbox with one heading per letter by default. For a
small listing -- e.g. a single changelog version -- that grouping can add
more noise than it saves navigation, so it can be turned off:

..  code-block::

    .. genindex::
        :scope: Changelog/12.4/
        :no-letter-index:

Index terms on sections
=======================

Independently of any ``genindex`` page, every term from an ``index`` entry
is also recorded on the section it resolves to (the next heading following
the directive, or the document's own top-level section if none follows).
A theme's section template can expose these as a search-key data attribute,
e.g. ``data-guides-index-terms="configuration,file"``.

This makes the terms available to external tooling that crawls the
rendered HTML rather than the reST source -- for example, a custom search
engine such as TYPO3's Elasticsearch integration can pick up the attribute
and index a section under the same terms an author tagged it with via
``index``, without needing its own copy of the genindex logic.
