<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\DependencyInjection;

use phpDocumentor\Guides\Compiler\Compiler;
use phpDocumentor\Guides\NodeRenderers\DelegatingNodeRenderer;
use phpDocumentor\Guides\Parser;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

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
    }
}
