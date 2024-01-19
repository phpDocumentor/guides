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

final class TestExtension extends Extension implements CompilerPassInterface
{
    /** @param array<mixed> $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
    }

    public function process(ContainerBuilder $container): void
    {
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
