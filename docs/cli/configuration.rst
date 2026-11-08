..  include:: /include.rst.txt

=============
Configuration
=============

The cli tool is able to read configuration from a ``guides.xml`` file.
You should put this file in the current working directory where you execute
the tool. Generally speaking, this is the root of your project.

Using a configuration file allows you to set project-specific settings and
keep them under version control. Not all options available in the configuration
are available on the command line. Such as the :ref:`extension configuration`.

.. note::

    The ``guides.xml`` file is not required. If it is not present, the tool
    will use the default configuration.

Global configuration
====================

Settings that you want to have regardless of the documentation you are
building should live in ``guides.xml`` in the current working directory.

.. literalinclude:: ./basic-config.xml
   :language: xml

See the ``guides.xsd`` file for all available config options.

Excluding files
===============

Files and directories below the input directory that should not be rendered can be excluded, in ``guides.xml`` or
with the ``--exclude-path`` option. A path without wildcards excludes that file, or that directory with everything in
it. A path with wildcards is a glob, where ``*`` matches within a directory and ``**/`` matches any number of
directories.

.. code-block:: xml

    <exclude>
        <path>_build</path>
        <path>drafts/*.rst</path>
    </exclude>

reStructuredText and Markdown in one project
============================================

The ``input-format`` setting takes a comma-separated list. With ``rst,md``, every ``.rst`` file is parsed as
reStructuredText and every ``.md`` file as Markdown.

.. code-block:: xml

    <guides input-format="rst,md">
        <exclude>
            <path>AGENTS.md</path>
        </exclude>
    </guides>

*   The index file can be written in either format, ``index.rst`` or ``index.md``.
*   A link to a page of the other format names its file, with the extension of that format: ``setup.rst`` in a
    Markdown link, ``setup.md`` in a reStructuredText hyperlink.
*   Two files that differ only in their extension, ``setup.rst`` and ``setup.md``, stop the run with an error; rename or
    exclude one.
*   Markdown files that are not documentation, like an ``AGENTS.md`` or a ``README.md`` next to the documentation,
    become pages as soon as ``md`` is in the list. Exclude them.

.. _violation_formats_ci:

Annotations in GitHub Actions and GitLab CI
===========================================

Problems found in the documentation source, like an empty code block or a malformed table, can be shown on the lines
of a pull request in GitHub, or of a merge request in GitLab. Set the ``violation-format`` setting in ``guides.xml``, or
pass the ``--violation-format`` option, and run the tool from the root of the repository. The problems are still logged
as before.

GitHub Actions
--------------

With ``github``, one line per problem is printed at the end of the run, which GitHub turns into an annotation. Its title
is the identifier of the check, like ``rst.code-block.empty``.

.. code-block:: yaml

    - run: vendor/bin/guides run Documentation --violation-format=github

GitLab CI
---------

With ``gitlab``, the problems are written to a code quality report, ``gl-code-quality-report.json`` in the current
working directory. Set another file with the ``violation-file`` setting or the ``--violation-file`` option. Declare the
file as code quality report of the job, so GitLab shows the problems in the merge request:

.. code-block:: yaml

    docs:
      script:
        - vendor/bin/guides run Documentation --violation-format=gitlab
      artifacts:
        when: always
        reports:
          codequality: gl-code-quality-report.json

Extensions can add more formats, see :ref:`custom_violation_formats`.

Extension configuration
=======================

Some extensions allow extra configuration to be added to the ``guides.xml``

.. literalinclude:: ./extension-config.xml
   :language: xml

.. hint::

   If you want to learn more about the extensions, see the :doc:`/developers/extensions/index` documentation.
