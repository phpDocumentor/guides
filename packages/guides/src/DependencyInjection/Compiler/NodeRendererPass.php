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

namespace phpDocumentor\Guides\DependencyInjection\Compiler;

use phpDocumentor\Guides\NodeRenderers\TemplateNodeRenderer;
use phpDocumentor\Guides\TemplateRenderer;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use function is_array;
use function sprintf;

final class NodeRendererPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definitions = [];
        if (is_array($container->getParameter('phpdoc.guides.node_templates')) === false) {
            throw new RuntimeException('phpdoc.guides.node_templates must be an array');
        }

        foreach ($container->getParameter('phpdoc.guides.node_templates') as $nodeTemplate) {
            $definition = new Definition(
                TemplateNodeRenderer::class,
                [
                    '$renderer' => new Reference(TemplateRenderer::class),
                    '$template' => $nodeTemplate['file'],
                    '$nodeClass' => $nodeTemplate['node'],
                ],
            );
             $definition->addTag('phpdoc.guides.noderenderer.' . $nodeTemplate['format']);
            $definitions[sprintf('phpdoc.guides.noderenderer.%s.%s', $nodeTemplate['format'], $nodeTemplate['node'])] = $definition;
        }

        $container->addDefinitions($definitions);
    }
}
