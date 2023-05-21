<?php

declare(strict_types=1);

use League\Tactician\CommandBus;
use phpDocumentor\Guides\Compiler\Compiler;
use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Compiler\DocumentNodeTraverser;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Compiler\NodeTransformers\CustomNodeTransformerFactory;
use phpDocumentor\Guides\Compiler\NodeTransformers\NodeTransformerFactory;
use phpDocumentor\Guides\Meta\ProjectMeta;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\NodeRenderers\DefaultNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\DelegatingNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\DocumentNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\MenuEntryRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\MenuNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\SpanNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\TableNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\InMemoryNodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\NodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\NodeRendererFactoryAware;
use phpDocumentor\Guides\Parser;
use phpDocumentor\Guides\Renderer\HtmlRenderer;
use phpDocumentor\Guides\Renderer\InMemoryRendererFactory;
use phpDocumentor\Guides\Renderer\IntersphinxRenderer;
use phpDocumentor\Guides\Renderer\LatexRenderer;
use phpDocumentor\Guides\Renderer\TypeRendererFactory;
use phpDocumentor\Guides\TemplateRenderer;
use phpDocumentor\Guides\Twig\AssetsExtension;
use phpDocumentor\Guides\Twig\EnvironmentBuilder;
use phpDocumentor\Guides\Twig\ThemeManager;
use phpDocumentor\Guides\Twig\TwigTemplateRenderer;
use phpDocumentor\Guides\UrlGenerator;
use phpDocumentor\Guides\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Twig\Loader\FilesystemLoader;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('phpdoc.guides.base_template_paths', [__DIR__ . '/../../../guides/resources/template/html']);

    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()

        ->instanceof(NodeRendererFactoryAware::class)
        ->tag('phpdoc.guides.noderendererfactoryaware')

        ->instanceof(CompilerPass::class)
        ->tag('phpdoc.guides.compiler.passes')

        ->instanceof(NodeTransformer::class)
        ->tag('phpdoc.guides.compiler.nodeTransformers')

        ->load(
            'phpDocumentor\\Guides\\Compiler\\NodeTransformers\\',
            '%vendor_dir%/phpdocumentor/guides/src/Compiler/NodeTransformers/*Transformer.php',
        )

        ->load(
            'phpDocumentor\\Guides\\Compiler\\Passes\\',
            '%vendor_dir%/phpdocumentor/guides/src/Compiler/Passes/*Pass.php',
        )

        ->load(
            'phpDocumentor\\Guides\\NodeRenderers\\',
            '%vendor_dir%/phpdocumentor/guides/src/NodeRenderers',
        )

        ->set(Metas::class)
        ->set(UrlGeneratorInterface::class, UrlGenerator::class)

        ->set(Parser::class)
        ->arg('$parserStrategies', tagged_iterator('phpdoc.guides.parser.markupLanguageParser'))

        ->set(Compiler::class)
        ->arg('$passes', tagged_iterator('phpdoc.guides.compiler.passes'))

        ->set(NodeTransformerFactory::class, CustomNodeTransformerFactory::class)
        ->arg('$transformers', tagged_iterator('phpdoc.guides.compiler.nodeTransformers'))

        ->set(DocumentNodeTraverser::class)


        ->set(UrlGenerator::class)

        ->set(HtmlRenderer::class)
        ->tag('phpdoc.renderer.typerenderer')
        ->args(
            ['$commandBus' => service(CommandBus::class)],
        )
        ->set(LatexRenderer::class)
        ->tag('phpdoc.renderer.typerenderer')
        ->args(
            ['$commandBus' => service(CommandBus::class)],
        )

        ->set(IntersphinxRenderer::class)
        ->tag('phpdoc.renderer.typerenderer')

        ->set(DocumentNodeRenderer::class)
        ->tag('phpdoc.guides.noderenderer.html')
        ->set(SpanNodeRenderer::class)
        ->tag('phpdoc.guides.noderenderer.html')
        ->set(TableNodeRenderer::class)
        ->tag('phpdoc.guides.noderenderer.html')
        ->set(MenuNodeRenderer::class)
        ->tag('phpdoc.guides.noderenderer.html')
        ->set(MenuEntryRenderer::class)
        ->tag('phpdoc.guides.noderenderer.html')

        ->set(ProjectMeta::class)

        ->set(InMemoryNodeRendererFactory::class)
        ->args([
            '$nodeRenderers' => tagged_iterator('phpdoc.guides.noderenderer.html'),
            '$defaultNodeRenderer' => new Reference(DefaultNodeRenderer::class),
        ])
        ->alias(NodeRendererFactory::class, InMemoryNodeRendererFactory::class)

         ->set(InMemoryRendererFactory::class)
        ->arg('$renderSets', tagged_iterator('phpdoc.renderer.typerenderer'))
        ->alias(TypeRendererFactory::class, InMemoryRendererFactory::class)


        ->set(AssetsExtension::class)
        ->arg('$nodeRenderer', service(DelegatingNodeRenderer::class))
        ->tag('twig.extension')
        ->autowire()

        ->set(ThemeManager::class)
        ->arg('$filesystemLoader', service(FilesystemLoader::class))
        ->arg(
            '$defaultPaths',
            param('phpdoc.guides.base_template_paths'),
        )

        ->set(FilesystemLoader::class)
        ->arg(
            '$paths',
            param('phpdoc.guides.base_template_paths'),
        )

        ->set(EnvironmentBuilder::class)
        ->arg('$extensions', tagged_iterator('twig.extension'))
        ->arg('$themeManager', service(ThemeManager::class))

        ->set(TemplateRenderer::class, TwigTemplateRenderer::class)
        ->arg('$environmentBuilder', new Reference(EnvironmentBuilder::class));
};
