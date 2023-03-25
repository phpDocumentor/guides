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

namespace phpDocumentor\Guides\Setup;

use ArrayObject;
use phpDocumentor\Guides\Configuration;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\NodeRenderers\DefaultNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\DocumentNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\SpanNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\TableNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\TocEntryRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\TocNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\InMemoryNodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\LazyNodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\NodeRenderers\NodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\TemplateNodeRenderer;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Parser;
use phpDocumentor\Guides\References\ReferenceResolver;
use phpDocumentor\Guides\References\Resolver\DocResolver;
use phpDocumentor\Guides\References\Resolver\RefResolver;
use phpDocumentor\Guides\Renderer;
use phpDocumentor\Guides\Renderer\InventoryRenderer;
use phpDocumentor\Guides\RestructuredText\NodeRenderers\Html\CollectionNodeRenderer;
use phpDocumentor\Guides\Twig\TwigRenderer;
use phpDocumentor\Guides\Renderer\OutputFormatRenderer;
use phpDocumentor\Guides\Renderer\TemplateRenderer;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\NodeRenderers\Html\AdmonitionNodeRenderer;
use phpDocumentor\Guides\RestructuredText\NodeRenderers\Html\ContainerNodeRenderer;
use phpDocumentor\Guides\RestructuredText\NodeRenderers\Html\SidebarNodeRenderer;
use phpDocumentor\Guides\RestructuredText\NodeRenderers\Html\TopicNodeRenderer;
use phpDocumentor\Guides\Twig\AssetsExtension;
use phpDocumentor\Guides\Twig\EnvironmentBuilder;
use phpDocumentor\Guides\UrlGenerator;
use Psr\Log\Test\TestLogger;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class QuickStart
{
    public static function createRstParser(): Parser
    {
        return new Parser(
            new UrlGenerator(),
            [
                MarkupLanguageParser::createInstance()
            ]
        );
    }

    public static function createRenderer(Metas $metas): Renderer
    {
        $logger = new TestLogger();
        /** @var ArrayObject<array-key, NodeRenderer<Node>> $nodeRenderers */
        $nodeRenderers = new ArrayObject();
        $nodeFactoryCallback = static fn(): NodeRendererFactory => new InMemoryNodeRendererFactory(
            $nodeRenderers,
            new DefaultNodeRenderer()
        );

        $twigBuilder = new EnvironmentBuilder();
        $renderer = new TwigRenderer(
            [
                new OutputFormatRenderer(
                    Renderer\HtmlTypeRenderer::TYPE,
                    new LazyNodeRendererFactory($nodeFactoryCallback),
                    new TemplateRenderer($twigBuilder)
                ),
                new OutputFormatRenderer(
                    Renderer\LatexTypeRenderer::TYPE,
                    new LazyNodeRendererFactory($nodeFactoryCallback),
                    new TemplateRenderer($twigBuilder)
                ),
            ],
            $twigBuilder
        );

        $nodeRenderers[] = new SpanNodeRenderer(
            $renderer,
            new ReferenceResolver([new DocResolver(), new RefResolver()]),
            $logger,
            new UrlGenerator()
        );
        $nodeRenderers[] = new TableNodeRenderer($renderer);
        $nodeRenderers[] = new AdmonitionNodeRenderer($renderer);
        $nodeRenderers[] = new ContainerNodeRenderer($renderer);
        $nodeRenderers[] = new CollectionNodeRenderer($renderer);
        $nodeRenderers[] = new SidebarNodeRenderer($renderer);
        $nodeRenderers[] = new TopicNodeRenderer($renderer);
        $nodeRenderers[] = new TocNodeRenderer($renderer);
        $nodeRenderers[] = new TocEntryRenderer($renderer);

        $config = new Configuration();
        foreach ($config->htmlNodeTemplates() as $node => $template) {
            $nodeRenderers[] = new TemplateNodeRenderer(
                $renderer,
                $template,
                $node
            );
        }

        $twigBuilder->setEnvironmentFactory(function () use ($logger, $renderer): Environment {
            $twig = new Environment(
                new FilesystemLoader(
                    [
                        __DIR__  . '/../../resources/template/html'
                    ]
                )
            );
            $twig->addExtension(new AssetsExtension(
                $logger,
                $renderer,
                new UrlGenerator(),
            ));

            return $twig;
        });

        return $renderer;
    }
}
