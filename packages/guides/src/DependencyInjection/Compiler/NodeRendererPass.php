<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\DependencyInjection\Compiler;

use phpDocumentor\Guides\NodeRenderers\NodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\TemplateNodeRenderer;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\AnnotationListNode;
use phpDocumentor\Guides\Nodes\CitationNode;
use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\DefinitionListNode;
use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\FieldListNode;
use phpDocumentor\Guides\Nodes\FigureNode;
use phpDocumentor\Guides\Nodes\FootnoteNode;
use phpDocumentor\Guides\Nodes\ImageNode;
use phpDocumentor\Guides\Nodes\Inline\AbbreviationInlineNode;
use phpDocumentor\Guides\Nodes\Inline\CitationInlineNode;
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\EmphasisInlineNode;
use phpDocumentor\Guides\Nodes\Inline\FootnoteInlineNode;
use phpDocumentor\Guides\Nodes\Inline\GenericTextRoleInlineNode;
use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use phpDocumentor\Guides\Nodes\Inline\LiteralInlineNode;
use phpDocumentor\Guides\Nodes\Inline\NewlineInlineNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\StrongInlineNode;
use phpDocumentor\Guides\Nodes\Inline\VariableInlineNode;
use phpDocumentor\Guides\Nodes\Inline\WhitespaceInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\ListItemNode;
use phpDocumentor\Guides\Nodes\ListNode;
use phpDocumentor\Guides\Nodes\LiteralBlockNode;
use phpDocumentor\Guides\Nodes\Metadata\AddressNode;
use phpDocumentor\Guides\Nodes\Metadata\AuthorNode;
use phpDocumentor\Guides\Nodes\Metadata\AuthorsNode;
use phpDocumentor\Guides\Nodes\Metadata\ContactNode;
use phpDocumentor\Guides\Nodes\Metadata\CopyrightNode;
use phpDocumentor\Guides\Nodes\Metadata\DateNode;
use phpDocumentor\Guides\Nodes\Metadata\MetaNode;
use phpDocumentor\Guides\Nodes\Metadata\NoCommentsNode;
use phpDocumentor\Guides\Nodes\Metadata\NoSearchNode;
use phpDocumentor\Guides\Nodes\Metadata\OrganizationNode;
use phpDocumentor\Guides\Nodes\Metadata\OrphanNode;
use phpDocumentor\Guides\Nodes\Metadata\RevisionNode;
use phpDocumentor\Guides\Nodes\Metadata\TocDepthNode;
use phpDocumentor\Guides\Nodes\Metadata\TopicNode;
use phpDocumentor\Guides\Nodes\Metadata\VersionNode;
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
        AnchorNode::class => 'inline/anchor.%s.twig',
        FigureNode::class => 'body/figure.%s.twig',
        MetaNode::class => 'structure/header/meta.%s.twig',
        ParagraphNode::class => 'body/paragraph.%s.twig',
        QuoteNode::class => 'body/quote.%s.twig',
        SeparatorNode::class => 'body/separator.%s.twig',
        TitleNode::class => 'structure/header-title.%s.twig',
        SectionNode::class => 'structure/section.%s.twig',
        DocumentNode::class => 'structure/document.%s.twig',
        ImageNode::class => 'body/image.%s.twig',
        CodeNode::class => 'body/code.%s.twig',
        DefinitionListNode::class => 'body/definition-list.%s.twig',
        DefinitionNode::class => 'body/definition.%s.twig',
        FieldListNode::class => 'body/field-list.%s.twig',
        ListNode::class => 'body/list/list.%s.twig',
        ListItemNode::class => 'body/list/list-item.%s.twig',
        LiteralBlockNode::class => 'body/literal-block.%s.twig',
        CitationNode::class => 'body/citation.%s.twig',
        FootnoteNode::class => 'body/footnote.%s.twig',
        AnnotationListNode::class => 'body/annotation-list.%s.twig',
        // Inline
        InlineCompoundNode::class => 'inline/inline-node.%s.twig',
        AbbreviationInlineNode::class => 'inline/textroles/abbreviation.%s.twig',
        CitationInlineNode::class => 'inline/citation.%s.twig',
        DocReferenceNode::class => 'inline/doc.%s.twig',
        EmphasisInlineNode::class => 'inline/emphasis.%s.twig',
        FootnoteInlineNode::class => 'inline/footnote.%s.twig',
        HyperLinkNode::class => 'inline/link.%s.twig',
        LiteralInlineNode::class => 'inline/literal.%s.twig',
        NewlineInlineNode::class => 'inline/newline.%s.twig',
        WhitespaceInlineNode::class => 'inline/nbsp.%s.twig',
        PlainTextInlineNode::class => 'inline/plain-text.%s.twig',
        ReferenceNode::class => 'inline/ref.%s.twig',
        StrongInlineNode::class => 'inline/strong.%s.twig',
        VariableInlineNode::class => 'inline/variable.%s.twig',
        GenericTextRoleInlineNode::class => 'inline/textroles/generic.%s.twig',
        // Output as Metatags
        AuthorNode::class => 'structure/header/author.%s.twig',
        CopyrightNode::class => 'structure/header/copyright.%s.twig',
        DateNode::class => 'structure/header/date.%s.twig',
        NoSearchNode::class => 'structure/header/no-search.%s.twig',
        TopicNode::class => 'structure/header/topic.%s.twig',
        // No output in page header in HTML - might be output in i.e. LaTex
        AddressNode::class => 'structure/header/blank.%s.twig',
        AuthorsNode::class => 'structure/header/blank.%s.twig',
        ContactNode::class => 'structure/header/blank.%s.twig',
        NoCommentsNode::class => 'structure/header/blank.%s.twig',
        OrganizationNode::class => 'structure/header/blank.%s.twig',
        OrphanNode::class => 'structure/header/blank.%s.twig',
        RevisionNode::class => 'structure/header/blank.%s.twig',
        TocDepthNode::class => 'structure/header/blank.%s.twig',
        VersionNode::class => 'structure/header/blank.%s.twig',
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
