<?php

declare(strict_types=1);

use phpDocumentor\Guides\NodeRenderers\DelegatingNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\InMemoryNodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\NodeRendererFactory;
use phpDocumentor\Guides\References\ReferenceResolver;
use phpDocumentor\Guides\Renderer\HtmlRenderer;
use phpDocumentor\Guides\Renderer\InMemoryRendererFactory;
use phpDocumentor\Guides\Renderer\TypeRendererFactory;
use phpDocumentor\Guides\TemplateRenderer;
use phpDocumentor\Guides\Twig\AssetsExtension;
use phpDocumentor\Guides\Twig\EnvironmentBuilder;
use phpDocumentor\Guides\Twig\TwigTemplateRenderer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->instanceof(phpDocumentor\Guides\References\Resolver\Resolver::class)
        ->tag('phpdoc.guides.reference.resolver')
        ->instanceof(phpDocumentor\Guides\NodeRenderers\NodeRendererFactoryAware::class)
        ->tag('phpdoc.guides.noderendererfactoryaware')
        ->load(
            'phpDocumentor\\Guides\\References\\Resolver\\',
            '%vendor_dir%/phpdocumentor/guides/src/References/Resolver'
        )->load(
            'phpDocumentor\\Guides\\NodeRenderers\\',
            '%vendor_dir%/phpdocumentor/guides/src/NodeRenderers'
        )



        ->set(ReferenceResolver::class)

        ->set(HtmlRenderer::class)
        ->tag('phpdoc.renderer.typerenderer')
        ->args(
            ['$renderer' => service(DelegatingNodeRenderer::class)]
        )

        ->set(phpDocumentor\Guides\NodeRenderers\InMemoryNodeRendererFactory::class)
        ->args([
            '$nodeRenderers' => tagged_iterator('phpdoc.guides.noderenderer.html'),
            '$defaultNodeRenderer' => new Reference('phpDocumentor\Guides\NodeRenderers\DefaultNodeRenderer'),
        ])
        ->alias(NodeRendererFactory::class, InMemoryNodeRendererFactory::class)

         ->set(InMemoryRendererFactory::class)
        ->arg('$renderSets', tagged_iterator('phpdoc.renderer.typerenderer'))
        ->alias(TypeRendererFactory::class, InMemoryRendererFactory::class)


        ->set(AssetsExtension::class)
        ->arg('$nodeRenderer', service(DelegatingNodeRenderer::class))
        ->tag('twig.extension')
        ->autowire()



        ->set(EnvironmentBuilder::class)
        ->arg('$extensions', tagged_iterator('twig.extension'))

        ->set(TemplateRenderer::class, TwigTemplateRenderer::class)
        ->arg('$environmentBuilder', new Reference(EnvironmentBuilder::class));
};
