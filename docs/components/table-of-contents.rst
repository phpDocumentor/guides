..  include:: /include.rst.txt

=================
Table of contents
=================

A rendered manual says a great deal about each of its pages and nothing about
itself as a whole. A tool that wants to read one -- a search index, a chat
agent, a script collecting release notes -- has to crawl it to find out what
pages there are and how they hang together.

The ``toc`` output format writes that down once, as ``toc.json`` at the root
of the output: every page, in the order the ``toctree`` directives put it and
nested the way they nest it, with its title and the anchor a reference to it
is built from.

Enabling it
===========

The format lives in the ``phpdocumentor/guides-llms`` package, which collects
the output that describes a manual to the tools and language models that read
it rather than to a person. Enabling the extension is all there is to it --
the table of contents is then rendered beside whatever else the project
renders:

.. code-block:: xml

    <extension class="phpDocumentor\Guides\Llms\DependencyInjection\LlmsExtension"/>

What it looks like
==================

.. code-block:: json

    {
        "project": {
            "title": "My manual",
            "version": "2.0"
        },
        "pages": [
            {
                "path": "index",
                "html": "index.html",
                "title": "My manual",
                "anchor": "my-manual",
                "pages": [
                    {
                        "path": "Chapter/Page",
                        "html": "Chapter/Page.html",
                        "title": "A page",
                        "anchor": "a-page"
                    }
                ]
            },
            {
                "orphan": true,
                "path": "Orphan",
                "html": "Orphan.html",
                "title": "Orphan",
                "anchor": "orphan"
            }
        ]
    }

``path``
    Where the page is, without an extension and relative to ``toc.json``.

``html``, and one key per other output format
    The files the page was rendered to. A format is the extension it writes,
    so a project rendering HTML and Markdown names both; formats that write a
    single file for the whole project, such as ``interlink``, ``tex`` and
    ``toc`` itself, name no page.

``title``
    The title of the page.

``anchor``
    The label a reference to the page is built from: the explicit label of the
    first section if it has one, and otherwise the id derived from the title.

``pages``
    The pages this page's table of contents leads to. Left out entirely for a
    page that leads nowhere, rather than written as an empty list.

``orphan``
    Set on the pages no table of contents reaches. They are listed after the
    tree rather than dropped: the files are published either way.

Naming the same files elsewhere
===============================

Which files a page exists as is not a question peculiar to the table of
contents. :php:`phpDocumentor\Guides\Llms\Renderer\DocumentOutputFiles` is a
public service under its own class name, so anything else writing about pages
-- another JSON file, a template, a listener -- can inject it and answer the
question the same way rather than keep a second list of output formats.

Adding to it
============

A theme usually knows more about a project than the library does -- how its
pages are addressed, what else a page carries. Two events are dispatched
before the file is written, and a listener on either may add keys or correct
the ones that are there:

:php:`phpDocumentor\Guides\Llms\Event\ModifyTocProject`
    The ``project`` section, once, with the :php:`ProjectNode`.

:php:`phpDocumentor\Guides\Llms\Event\ModifyTocPage`
    Every page, the orphans included, with its :php:`DocumentEntryNode` and
    the parsed :php:`DocumentNode`. The page's own ``pages`` are nested after
    the event.

.. code-block:: php

    final class AddPermalink
    {
        public function __invoke(ModifyTocProject $event): void
        {
            $event->setProject([
                ...$event->getProject(),
                'permalink' => 'https://example.org/permalink/manual:{anchor}',
            ]);
        }
    }
