<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\DependencyInjection\Compiler;

use phpDocumentor\Guides\NodeRenderers\NodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\TemplateNodeRenderer;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\DefinitionListNode;
use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\FigureNode;
use phpDocumentor\Guides\Nodes\ImageNode;
use phpDocumentor\Guides\Nodes\ListItemNode;
use phpDocumentor\Guides\Nodes\ListNode;
use phpDocumentor\Guides\Nodes\LiteralBlockNode;
use phpDocumentor\Guides\Nodes\Metadata\MetaNode;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\QuoteNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\SeparatorNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\TemplateRenderer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class NodeRendererPass implements CompilerPassInterface
{
    private const HTML = [
        AnchorNode::class => 'inline/anchor.html.twig',
        FigureNode::class => 'body/figure.html.twig',
        MetaNode::class => 'structure/header/meta.html.twig',
        ParagraphNode::class => 'body/paragraph.html.twig',
        QuoteNode::class => 'body/quote.html.twig',
        SeparatorNode::class => 'body/separator.html.twig',
        TitleNode::class => 'structure/header-title.html.twig',
        SectionNode::class => 'structure/section.html.twig',
        DocumentNode::class => 'structure/document.html.twig',
        ImageNode::class => 'body/image.html.twig',
        CodeNode::class => 'body/code.html.twig',
        DefinitionListNode::class => 'body/definition-list.html.twig',
        DefinitionNode::class => 'body/definition.html.twig',
        ListNode::class => 'body/list/list.html.twig',
        ListItemNode::class => 'body/list/list-item.html.twig',
        LiteralBlockNode::class => 'body/literal-block.html.twig',
    ];

    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds('phpdoc.guides.noderendererfactoryaware') as $id => $tags) {
            $definition = $container->getDefinition($id);
            $definition->addMethodCall(
                'setNodeRendererFactory',
                [new Reference(NodeRendererFactory::class)],
            );
            $definition->clearTag('phpdoc.guides.noderendererfactoryaware');
        }

         $htmlRendererDefinitions = [];
        foreach (self::HTML as $node => $template) {
            $definition = new Definition(
                TemplateNodeRenderer::class,
                [
                    '$renderer' => new Reference(TemplateRenderer::class),
                    '$template' => $template,
                    '$nodeClass' => $node,
                ],
            );
            $definition->addTag('phpdoc.guides.noderenderer.html');

            $htmlRendererDefinitions['phpdoc.guides.noderenderer.html.' . $node] = $definition;
        }

         $container->addDefinitions($htmlRendererDefinitions);
    }
}
