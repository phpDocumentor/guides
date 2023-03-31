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
use phpDocumentor\Guides\NodeRenderers\DefaultNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\DelegatingNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\SpanNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\TableNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\TocEntryRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\TocNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\InMemoryNodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\LazyNodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\NodeRenderers\NodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\TemplateNodeRenderer;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Parser;
use phpDocumentor\Guides\References\ReferenceResolver;
use phpDocumentor\Guides\References\Resolver\DocResolver;
use phpDocumentor\Guides\References\Resolver\RefResolver;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\NodeRenderers\Html\AdmonitionNodeRenderer;
use phpDocumentor\Guides\RestructuredText\NodeRenderers\Html\CollectionNodeRenderer;
use phpDocumentor\Guides\RestructuredText\NodeRenderers\Html\ContainerNodeRenderer;
use phpDocumentor\Guides\RestructuredText\NodeRenderers\Html\SidebarNodeRenderer;
use phpDocumentor\Guides\RestructuredText\NodeRenderers\Html\TopicNodeRenderer;
use phpDocumentor\Guides\Twig\AssetsExtension;
use phpDocumentor\Guides\Twig\EnvironmentBuilder;
use phpDocumentor\Guides\Twig\TwigTemplateRenderer;
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
            [MarkupLanguageParser::createInstance()]
        );
    }

    /** @return NodeRenderer<DocumentNode> */
    public static function createRenderer(): NodeRenderer
    {
        $logger = new TestLogger();
        /** @var ArrayObject<array-key, NodeRenderer<Node>> $nodeRenderers */
        $nodeRenderers = new ArrayObject();
        $nodeFactoryCallback = static fn (): NodeRendererFactory => new InMemoryNodeRendererFactory(
            $nodeRenderers,
            new DefaultNodeRenderer()
        );

        $renderer = new DelegatingNodeRenderer();
        $renderer->setNodeRendererFactory(new LazyNodeRendererFactory($nodeFactoryCallback));

        $twigBuilder = new EnvironmentBuilder();
        $templateRenderer = new TwigTemplateRenderer(
            $twigBuilder
        );

        $nodeRenderers[] = new SpanNodeRenderer(
            $templateRenderer,
            new ReferenceResolver([new DocResolver(), new RefResolver()]),
            $logger,
            new UrlGenerator()
        );
        $nodeRenderers[] = new TableNodeRenderer($templateRenderer);
        $nodeRenderers[] = new AdmonitionNodeRenderer($templateRenderer);
        $nodeRenderers[] = new ContainerNodeRenderer($templateRenderer);
        $nodeRenderers[] = new CollectionNodeRenderer($templateRenderer);
        $nodeRenderers[] = new SidebarNodeRenderer($templateRenderer);
        $nodeRenderers[] = new TopicNodeRenderer($templateRenderer);
        $nodeRenderers[] = new TocNodeRenderer($templateRenderer);
        $nodeRenderers[] = new TocEntryRenderer($templateRenderer);

        $config = new Configuration();
        foreach ($config->htmlNodeTemplates() as $node => $template) {
            $nodeRenderers[] = new TemplateNodeRenderer(
                $templateRenderer,
                $template,
                $node
            );
        }

        $twigBuilder->setEnvironmentFactory(static function () use ($logger, $renderer): Environment {
            $twig = new Environment(
                new FilesystemLoader(
                    [
                        __DIR__ . '/../../resources/template/html/guides',
                    ]
                )
            );
            $twig->addExtension(new AssetsExtension(
                $logger,
                /** @var NodeRenderer<Node> $renderer */
                $renderer,
                new UrlGenerator(),
            ));

            return $twig;
        });

        return $renderer;
    }
}
