<?php

declare(strict_types=1);

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
use phpDocumentor\Guides\Nodes\Inline\ImageInlineNode;
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

return [
    AnchorNode::class => 'inline/anchor.rst.twig',
    FigureNode::class => 'body/figure.rst.twig',
    MetaNode::class => 'structure/header/meta.rst.twig',
    ParagraphNode::class => 'body/paragraph.rst.twig',
    QuoteNode::class => 'body/quote.rst.twig',
    SeparatorNode::class => 'body/separator.rst.twig',
    TitleNode::class => 'structure/header-title.rst.twig',
    SectionNode::class => 'structure/section.rst.twig',
    DocumentNode::class => 'structure/document.rst.twig',
    ImageNode::class => 'body/image.rst.twig',
    CodeNode::class => 'body/code.rst.twig',
    DefinitionListNode::class => 'body/definition-list.rst.twig',
    DefinitionNode::class => 'body/definition.rst.twig',
    FieldListNode::class => 'body/field-list.rst.twig',
    ListNode::class => 'body/list/list.rst.twig',
    ListItemNode::class => 'body/list/list-item.rst.twig',
    LiteralBlockNode::class => 'body/literal-block.rst.twig',
    CitationNode::class => 'body/citation.rst.twig',
    FootnoteNode::class => 'body/footnote.rst.twig',
    AnnotationListNode::class => 'body/annotation-list.rst.twig',
    // Inline
    ImageInlineNode::class => 'inline/image.rst.twig',
    InlineCompoundNode::class => 'inline/inline-node.rst.twig',
    AbbreviationInlineNode::class => 'inline/textroles/abbreviation.rst.twig',
    CitationInlineNode::class => 'inline/citation.rst.twig',
    DocReferenceNode::class => 'inline/doc.rst.twig',
    EmphasisInlineNode::class => 'inline/emphasis.rst.twig',
    FootnoteInlineNode::class => 'inline/footnote.rst.twig',
    HyperLinkNode::class => 'inline/link.rst.twig',
    LiteralInlineNode::class => 'inline/literal.rst.twig',
    NewlineInlineNode::class => 'inline/newline.rst.twig',
    WhitespaceInlineNode::class => 'inline/nbsp.rst.twig',
    PlainTextInlineNode::class => 'inline/plain-text.rst.twig',
    ReferenceNode::class => 'inline/ref.rst.twig',
    StrongInlineNode::class => 'inline/strong.rst.twig',
    VariableInlineNode::class => 'inline/variable.rst.twig',
    GenericTextRoleInlineNode::class => 'inline/textroles/generic.rst.twig',
    // Output as Metatags
    AuthorNode::class => 'structure/header/author.rst.twig',
    CopyrightNode::class => 'structure/header/copyright.rst.twig',
    DateNode::class => 'structure/header/date.rst.twig',
    NoSearchNode::class => 'structure/header/no-search.rst.twig',
    TopicNode::class => 'structure/header/topic.rst.twig',
    // No output in page header in tex - might be output in i.e. LaTex
    AddressNode::class => 'structure/header/blank.rst.twig',
    AuthorsNode::class => 'structure/header/blank.rst.twig',
    ContactNode::class => 'structure/header/blank.rst.twig',
    NoCommentsNode::class => 'structure/header/blank.rst.twig',
    OrganizationNode::class => 'structure/header/blank.rst.twig',
    OrphanNode::class => 'structure/header/blank.rst.twig',
    RevisionNode::class => 'structure/header/blank.rst.twig',
    TocDepthNode::class => 'structure/header/blank.rst.twig',
    VersionNode::class => 'structure/header/blank.rst.twig',
];
