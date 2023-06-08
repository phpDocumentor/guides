<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\DependencyInjection\Compiler;

use phpDocumentor\Guides\NodeRenderers\NodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\TemplateNodeRenderer;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\CitationNode;
use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\DefinitionListNode;
use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\FieldListNode;
use phpDocumentor\Guides\Nodes\FigureNode;
use phpDocumentor\Guides\Nodes\FootnoteNode;
use phpDocumentor\Guides\Nodes\ImageNode;
use phpDocumentor\Guides\Nodes\InlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\AbbreviationToken;
use phpDocumentor\Guides\Nodes\InlineToken\CitationInlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\DocReferenceNode;
use phpDocumentor\Guides\Nodes\InlineToken\EmphasisToken;
use phpDocumentor\Guides\Nodes\InlineToken\GenericTextRoleToken;
use phpDocumentor\Guides\Nodes\InlineToken\HyperLinkNode;
use phpDocumentor\Guides\Nodes\InlineToken\LiteralToken;
use phpDocumentor\Guides\Nodes\InlineToken\NbspToken;
use phpDocumentor\Guides\Nodes\InlineToken\NewlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\PlainTextToken;
use phpDocumentor\Guides\Nodes\InlineToken\ReferenceNode;
use phpDocumentor\Guides\Nodes\InlineToken\StrongEmphasisToken;
use phpDocumentor\Guides\Nodes\InlineToken\VariableInlineNode;
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
use phpDocumentor\Guides\Nodes\RubricNode;
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
        FieldListNode::class => 'body/field-list.html.twig',
        ListNode::class => 'body/list/list.html.twig',
        ListItemNode::class => 'body/list/list-item.html.twig',
        LiteralBlockNode::class => 'body/literal-block.html.twig',
        RubricNode::class => 'body/rubric.html.twig',
        CitationNode::class => 'body/citation.html.twig',
        FootnoteNode::class => 'body/footnote.html.twig',
        // Inline
        InlineNode::class => 'inline/inline-node.html.twig',
        AbbreviationToken::class => 'inline/textroles/abbreviation.html.twig',
        CitationInlineNode::class => 'inline/citation.html.twig',
        DocReferenceNode::class => 'inline/doc.html.twig',
        EmphasisToken::class => 'inline/emphasis.html.twig',
        HyperLinkNode::class => 'inline/link.html.twig',
        LiteralToken::class => 'inline/literal.html.twig',
        NewlineNode::class => 'inline/newline.html.twig',
        NbspToken::class => 'inline/nbsp.html.twig',
        PlainTextToken::class => 'inline/plain-text.html.twig',
        ReferenceNode::class => 'inline/ref.html.twig',
        StrongEmphasisToken::class => 'inline/strong.html.twig',
        VariableInlineNode::class => 'inline/variable.html.twig',
        GenericTextRoleToken::class => 'inline/textroles/generic.html.twig',
        // Output as Metatags
        AuthorNode::class => 'structure/header/author.html.twig',
        CopyrightNode::class => 'structure/header/copyright.html.twig',
        DateNode::class => 'structure/header/date.html.twig',
        NoSearchNode::class => 'structure/header/no-search.html.twig',
        TopicNode::class => 'structure/header/topic.html.twig',
        // No output in page header in HTML - might be output in i.e. LaTex
        AddressNode::class => 'structure/header/blank.html.twig',
        AuthorsNode::class => 'structure/header/blank.html.twig',
        ContactNode::class => 'structure/header/blank.html.twig',
        NoCommentsNode::class => 'structure/header/blank.html.twig',
        OrganizationNode::class => 'structure/header/blank.html.twig',
        OrphanNode::class => 'structure/header/blank.html.twig',
        RevisionNode::class => 'structure/header/blank.html.twig',
        TocDepthNode::class => 'structure/header/blank.html.twig',
        VersionNode::class => 'structure/header/blank.html.twig',
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
