<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\DependencyInjection;

use League\Tactician\CommandBus;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use phpDocumentor\Guides\Compiler\Compiler;
use phpDocumentor\Guides\Parser;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Test support for consumers and extensions of phpDocumentor Guides.
 *
 * Register this extension alongside the regular ones when building the container
 * in a test case. It is not meant for production containers.
 *
 * `load()` is empty, so the extension carries no configuration. Its `process()`
 * method changes three things:
 *
 * - makes {@see CommandBus}, {@see Parser}, {@see Compiler} and the
 *   `phpdoc.guides.output_node_renderer` service public, so a test can pull them
 *   out of the container directly;
 * - defines {@see ClockInterface} as a {@see MockClock} pinned to
 *   `2023-01-01 12:00:00`, overriding the `NativeClock` alias guides-cli
 *   registers, so rendered output containing a build date stays comparable
 *   against a fixture;
 * - registers Monolog's {@see TestHandler} as a public service and pushes it onto
 *   the {@see Logger}, so a test can assert on the records that were logged.
 *
 * Prerequisites: the container must already define a Monolog {@see Logger}
 * service, which `phpDocumentor\Guides\Cli\DependencyInjection\ApplicationExtension`
 * registers. `phpdocumentor/guides` itself does not require `monolog/monolog`;
 * add it to your `require-dev` if you register the logger yourself.
 *
 * See `docs/developers/extensions/testing.rst` for a worked example.
 */
final class TestExtension extends Extension implements CompilerPassInterface
{
    /** @param array<mixed> $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
    }

    public function process(ContainerBuilder $container): void
    {
        $container->getDefinition(CommandBus::class)->setPublic(true);
        $container->getDefinition(Parser::class)->setPublic(true);
        $container->getDefinition(Compiler::class)->setPublic(true);
        $container->getDefinition('phpdoc.guides.output_node_renderer')->setPublic(true);

        $clockDefinition = new Definition(MockClock::class, ['2023-01-01 12:00:00']);
        $container->setDefinition(ClockInterface::class, $clockDefinition);

        $container->register(TestHandler::class, TestHandler::class)->setPublic(true);
        $container->getDefinition(Logger::class)
            ->addMethodCall('pushHandler', [new Reference(TestHandler::class)]);
    }
}
