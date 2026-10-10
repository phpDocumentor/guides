..  include:: /include.rst.txt

=======================
Machine-readable output
=======================

A rendered manual is written for people. The tools that read it instead -- a
search index, a chat agent, a script collecting release notes -- have to crawl
its pages to find out what there is and how it hangs together.

The ``phpdocumentor/guides-machine-readable`` package writes output for these
tools, beside whatever else the project renders. The first such output is the
table of contents.

Enabling it
===========

Enabling the extension is all there is to it:

.. code-block:: xml

    <extension class="phpDocumentor\Guides\MachineReadable\DependencyInjection\MachineReadableExtension"/>

Table of contents
=================

The ``llm_toc`` output format writes the manual's structure down once, as
``toc.json`` at the root of the output: every page, in the order the
``toctree`` directives put it and nested the way they nest it, with its title
and the anchor a reference to it is built from.

What it looks like
------------------

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
    ``llm_toc`` itself, name no page.

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
-------------------------------

Which files a page exists as is not a question peculiar to the table of
contents. :php:`phpDocumentor\Guides\MachineReadable\Renderer\DocumentOutputFiles` is a
public service under its own class name, so anything else writing about pages
-- another JSON file, a template, a listener -- can inject it and answer the
question the same way rather than keep a second list of output formats.

Adding to it
------------

A theme usually knows more about a project than the library does -- how its
pages are addressed, what else a page carries. Two events are dispatched
before the file is written, and a listener on either may correct the values
that are there or add keys with ``setExtra()``:

:php:`phpDocumentor\Guides\MachineReadable\Event\ModifyTocProjectInfo`
    The ``project`` section as a
    :php:`phpDocumentor\Guides\MachineReadable\Toc\TocProject`, once, with
    the :php:`ProjectNode`.

:php:`phpDocumentor\Guides\MachineReadable\Event\ModifyTocPageEntry`
    Every page as a
    :php:`phpDocumentor\Guides\MachineReadable\Toc\PageDescriptor`, the
    orphans included, with its :php:`DocumentEntryNode` and the parsed
    :php:`DocumentNode`. The page's own ``pages`` are added after the event.

.. code-block:: php

    final class AddPermalink
    {
        public function __invoke(ModifyTocProjectInfo $event): void
        {
            $event->getProject()->setExtra(
                'permalink',
                'https://example.org/permalink/manual:{anchor}',
            );
        }
    }
