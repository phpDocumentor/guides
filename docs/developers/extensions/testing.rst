..  include:: /include.rst.txt

..  _extension_testing:

======================
Testing your extension
======================

Rendering is the observable behaviour of an extension, so the most useful test
builds a container, runs a source file through it and compares the output with a
fixture. ``phpdocumentor/guides`` ships
``phpDocumentor\Guides\DependencyInjection\TestExtension`` to make that possible:
it is a Symfony DI extension that adjusts a container for use from a test.

..  note::
    ``TestExtension`` is meant for test code only. Do not register it in the
    container your application builds at runtime.

What it does
============

``TestExtension`` implements ``CompilerPassInterface`` and leaves ``load()``
empty, so it takes no configuration. Its ``process()`` method changes three
things:

*   It marks ``League\Tactician\CommandBus``, ``phpDocumentor\Guides\Parser``,
    ``phpDocumentor\Guides\Compiler\Compiler`` and the
    ``phpdoc.guides.output_node_renderer`` service **public**. Without this a
    test cannot pull them out of the compiled container: Guides sets no explicit
    visibility on them, so Symfony's default applies and they are private.
*   It defines the ``Psr\Clock\ClockInterface`` service as a
    ``Symfony\Component\Clock\MockClock`` pinned to ``2023-01-01 12:00:00``,
    overriding the ``NativeClock`` alias that ``phpdocumentor/guides-cli``
    registers. That clock is what ``SettingsBuilder`` reads to stamp the
    ``ProjectNode`` with a date, so output rendering a build date stays
    byte-comparable against a fixture between runs.
*   It registers ``Monolog\Handler\TestHandler`` as a public service and pushes it
    onto the ``Monolog\Logger``, so a test can assert on the log records a build
    produced — for instance that a malformed directive emitted exactly one
    warning.

Nothing else changes: parsing, compiling and rendering behave as they do in a
normal run.

Prerequisites
=============

``TestExtension`` adjusts services that another extension has to define first:

*   ``CommandBus``, ``Parser``, ``Compiler`` and
    ``phpdoc.guides.output_node_renderer`` come from ``GuidesExtension`` in
    ``phpdocumentor/guides`` itself.
*   The ``Monolog\Logger`` service comes from ``ApplicationExtension`` in
    ``phpdocumentor/guides-cli``. If you build a container without that extension,
    register a ``Monolog\Logger`` service yourself before ``TestExtension`` runs,
    or ``process()`` fails with a ``ServiceNotFoundException``.

``phpdocumentor/guides`` does not require ``monolog/monolog``; it is a dependency
of ``phpdocumentor/guides-cli``. If your package depends on ``phpdocumentor/guides``
alone, add ``monolog/monolog`` to your ``require-dev``.

The example below uses ``ContainerFactory`` and ``ApplicationExtension``, which
are ``phpdocumentor/guides-cli`` classes, so that package belongs in your
``require-dev`` as well unless you already require it. Using it also settles the
``Monolog\Logger`` prerequisite, because ``ApplicationExtension`` registers that
service.

Usage
=====

Register ``TestExtension`` together with the extensions under test when you
create the container:

..  code-block:: php
    :caption: your-extension/tests/RenderingTest.php

    use phpDocumentor\Guides\Cli\DependencyInjection\ApplicationExtension;
    use phpDocumentor\Guides\Cli\DependencyInjection\ContainerFactory;
    use phpDocumentor\Guides\Compiler\Compiler;
    use phpDocumentor\Guides\DependencyInjection\TestExtension;
    use phpDocumentor\Guides\Parser;
    use PHPUnit\Framework\TestCase;
    use YourName\YourExtension\DependencyInjection\YourExtension;

    final class RenderingTest extends TestCase
    {
        public function testItRendersTheDirective(): void
        {
            $containerFactory = new ContainerFactory([
                new ApplicationExtension(),
                new YourExtension(),
                new TestExtension(),
            ]);

            $container = $containerFactory->create(__DIR__ . '/../vendor');

            $parser = $container->get(Parser::class);
            $compiler = $container->get(Compiler::class);

            // ... parse a fixture, compile it, render it and compare the output
        }
    }

Asserting on log records
------------------------

Because ``TestHandler`` is public and attached to the logger, the records of a
build are available from the same container:

..  code-block:: php

    use Monolog\Handler\TestHandler;

    $handler = $container->get(TestHandler::class);

    self::assertTrue($handler->hasWarningThatContains('Unknown directive'));
    self::assertCount(1, $handler->getRecords());

Within this repository
======================

``tests/ApplicationTestCase.php`` builds its container the same way, and the
functional and integration suites inherit from it. The `Writing tests
<https://github.com/phpDocumentor/guides/blob/main/docs/contributions/writing-tests.rst>`__
chapter describes how those suites are laid out.
