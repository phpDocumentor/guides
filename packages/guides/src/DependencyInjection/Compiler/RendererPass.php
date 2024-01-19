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

use phpDocumentor\Guides\NodeRenderers\DefaultNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\DelegatingNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\InMemoryNodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\PreRenderers\PreNodeRendererFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use function sprintf;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

final class RendererPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definitions = [];
        foreach ($container->findTaggedServiceIds('phpdoc.renderer.typerenderer') as $id => $tags) {
            foreach ($tags as $tag) {
                if (isset($tag['noderender_tag']) === false) {
                    continue;
                }

                $definitions[sprintf('phpdoc.guides.noderenderer.factory.%s', $tag['format'])] = $this->createNodeRendererFactory($tag);
                $definitions[sprintf('phpdoc.guides.noderenderer.prefactory.%s', $tag['format'])] = $this->createPreNodeRendererFactory($tag);
                $definitions[sprintf('phpdoc.guides.noderenderer.delegating.%s', $tag['format'])] = $this->createDelegatingNodeRender($tag);
                $definitions[sprintf('phpdoc.guides.noderenderer.default.%s', $tag['format'])] = (new Definition(DefaultNodeRenderer::class))->setAutowired(true)
                    ->addMethodCall('setNodeRendererFactory', [new Reference(sprintf('phpdoc.guides.noderenderer.factory.%s', $tag['format']))])
                    ->addTag(sprintf('phpdoc.guides.noderenderer.%s', $tag['format']));
            }
        }

        $container->addDefinitions($definitions);
    }

    /** @param array{format: string} $tag */
    private function createDelegatingNodeRender(array $tag): Definition
    {
        return (new Definition(DelegatingNodeRenderer::class))
            ->addTag('phpdoc.guides.output_node_renderer', ['format' => $tag['format']])
            ->addMethodCall('setNodeRendererFactory', [new Reference(sprintf('phpdoc.guides.noderenderer.factory.%s', $tag['format']))]);
    }

    /** @param array{format: string, noderender_tag: string} $tag */
    private function createNodeRendererFactory(array $tag): Definition
    {
        return new Definition(
            InMemoryNodeRendererFactory::class,
            [
                '$nodeRenderers' => tagged_iterator($tag['noderender_tag']),
                '$defaultNodeRenderer' => new Reference(sprintf('phpdoc.guides.noderenderer.default.%s', $tag['format'])),
            ],
        );
    }

    /** @param array{format: string, noderender_tag: string} $tag */
    private function createPreNodeRendererFactory(array $tag): Definition
    {
        return (new Definition(
            PreNodeRendererFactory::class,
            [
                '$innerFactory' => new Reference('.inner'),
                '$preRenderers' => tagged_iterator('phpdoc.guides.prerenderer'),
            ],
        ))
            ->setDecoratedService(sprintf('phpdoc.guides.noderenderer.factory.%s', $tag['format']));
    }
}
