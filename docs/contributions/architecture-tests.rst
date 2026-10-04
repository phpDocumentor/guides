..  include:: /include.rst.txt

==================
Architecture tests
==================

Next to unit, functional and integration tests, this project also has a set of architecture tests. Where the other
kinds of tests verify *behaviour*, architecture tests verify *structure*: they make sure the boundaries between
packages (and, where needed, within a package) are respected as the codebase grows.

Architecture tests are written with `phpat <https://www.phpat.dev/>`_, which runs as an extension of PHPStan. This
means architecture violations are reported as part of ``make phpstan``, alongside the regular static analysis
errors, instead of requiring a separate tool or command.

Why architecture tests?
========================

This mono repository is split into several packages (see :doc:`/contributions/monorepository-layout`), each of which
is published as its own Composer package. The packages are only allowed to depend on each other in specific,
intentional directions, for example:

- ``guides-cli`` may depend on ``guides``, ``guides-restructured-text`` and ``guides-markdown``.
- ``guides`` is the core package and must not depend on any of the other packages.
- ``guides-restructured-text`` and ``guides-markdown`` may only depend on ``guides``.

Nothing in PHP itself prevents a class from reaching across these boundaries with an extra ``use`` statement.
Architecture tests make these rules explicit and enforce them automatically, so an accidental dependency is caught
in CI instead of being discovered much later as a circular dependency or a package that can no longer be installed
on its own.

Two levels of rules
====================

Architecture rules live in two places:

``tests/Architecture``
  Rules at this level guard the boundaries *between* packages. They describe which package is allowed to depend on
  which other package, mirroring the list above.

``packages/<package>/tests/Architecture``
  Rules at this level guard the structure *inside* a single package, for example keeping domain classes free of
  infrastructure concerns, or making sure implementations depend on interfaces rather than the other way around.
  These rules are specific to the package they live in and are only meaningful in that context.

Both levels are plain PHP classes implementing ``PHPat\Test\TestCaseRule``, picked up by PHPStan through the paths
configured in ``phpstan.neon``.

Writing a rule
==============

A rule selects a group of classes and asserts what they may or may not depend on. For example, the following rule
keeps the core ``Guides`` package free of dependencies on the other packages::

    <?php

    declare(strict_types=1);

    namespace phpDocumentor\Guides\Architecture;

    use PHPat\Selector\Selector;
    use PHPat\Test\Builder\Rule;
    use PHPat\Test\PHPat;

    final class GuidesArchitectureTest
    {
        public function core_does_not_depend_on_other_packages(): Rule
        {
            return PHPat::rule()
                ->classes(Selector::inNamespace('phpDocumentor\Guides'))
                ->shouldNotDependOn()
                ->classes(
                    Selector::inNamespace('phpDocumentor\Guides\Cli'),
                    Selector::inNamespace('phpDocumentor\Guides\RestructuredText'),
                    Selector::inNamespace('phpDocumentor\Guides\Markdown'),
                    Selector::inNamespace('phpDocumentor\Guides\Graphs'),
                );
        }
    }

Running the checks
===================

Architecture rules run together with the rest of the static analysis::

    make phpstan

A violated rule is reported the same way a regular PHPStan error would be, including the file and line of the
offending dependency.
