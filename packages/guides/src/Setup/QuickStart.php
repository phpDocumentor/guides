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
use phpDocumentor\Guides\NodeRenderers\Html\DocumentNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\SpanNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\TableNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\InMemoryNodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\LazyNodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\NodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\TemplateNodeRenderer;
use phpDocumentor\Guides\Parser;
use phpDocumentor\Guides\References\ReferenceResolver;
use phpDocumentor\Guides\References\Resolver\DocResolver;
use phpDocumentor\Guides\Renderer;
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

    public static function createRenderer(): Renderer
    {
        $logger = new TestLogger();
        $nodeRenderers = new ArrayObject();
        $nodeFactoryCallback = static fn(): NodeRendererFactory => new InMemoryNodeRendererFactory(
            $nodeRenderers,
            new DefaultNodeRenderer()
        );

        $twigBuilder = new EnvironmentBuilder();
        $renderer = new TwigRenderer(
            [
                new OutputFormatRenderer(
                    'html',
                    new LazyNodeRendererFactory($nodeFactoryCallback),
                    new TemplateRenderer($twigBuilder)
                ),
            ],
            $twigBuilder
        );

        $nodeRenderers[] = new DocumentNodeRenderer($renderer);
        $nodeRenderers[] = new SpanNodeRenderer(
            $renderer,
            new ReferenceResolver([new DocResolver()]),
            $logger,
            new UrlGenerator()
        );
        $nodeRenderers[] = new TableNodeRenderer($renderer);
        $nodeRenderers[] = new AdmonitionNodeRenderer($renderer);
        $nodeRenderers[] = new ContainerNodeRenderer($renderer);
        $nodeRenderers[] = new SidebarNodeRenderer($renderer);
        $nodeRenderers[] = new TopicNodeRenderer($renderer);

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
                        __DIR__  . '/../../resources/template'
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
