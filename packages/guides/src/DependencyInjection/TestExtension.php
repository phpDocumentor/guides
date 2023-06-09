<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\DependencyInjection;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use phpDocumentor\Guides\Compiler\Compiler;
use phpDocumentor\Guides\NodeRenderers\DelegatingNodeRenderer;
use phpDocumentor\Guides\Parser;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

class TestExtension extends Extension implements CompilerPassInterface
{
    /** @param array<mixed> $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
    }

    public function process(ContainerBuilder $container): void
    {
        $container->getDefinition(Parser::class)->setPublic(true);
        $container->getDefinition(Compiler::class)->setPublic(true);
        $container->getDefinition(DelegatingNodeRenderer::class)->setPublic(true);

        $container->register(TestHandler::class, TestHandler::class)->setPublic(true);
        $container->getDefinition(Logger::class)
            ->addMethodCall('pushHandler', [new Reference(TestHandler::class)]);
    }
}
