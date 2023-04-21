<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use phpDocumentor\Guides\NodeRenderers\DelegatingNodeRenderer;
use phpDocumentor\Guides\Parser;

class TestExtension extends Extension
{
    /** @param array<mixed> $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
    }

    public function process(ContainerBuilder $container): void
    {
        $container->getDefinition(Parser::class)->setPublic(true);
        $container->getDefinition(DelegatingNodeRenderer::class)->setPublic(true);
    }
}
