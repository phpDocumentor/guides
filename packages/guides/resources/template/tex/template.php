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
    AnchorNode::class => 'inline/anchor.tex.twig',
    FigureNode::class => 'body/figure.tex.twig',
    MetaNode::class => 'structure/header/meta.tex.twig',
    ParagraphNode::class => 'body/paragraph.tex.twig',
    QuoteNode::class => 'body/quote.tex.twig',
    SeparatorNode::class => 'body/separator.tex.twig',
    TitleNode::class => 'structure/header-title.tex.twig',
    SectionNode::class => 'structure/section.tex.twig',
    DocumentNode::class => 'structure/document.tex.twig',
    ImageNode::class => 'body/image.tex.twig',
    CodeNode::class => 'body/code.tex.twig',
    DefinitionListNode::class => 'body/definition-list.tex.twig',
    DefinitionNode::class => 'body/definition.tex.twig',
    FieldListNode::class => 'body/field-list.tex.twig',
    ListNode::class => 'body/list/list.tex.twig',
    ListItemNode::class => 'body/list/list-item.tex.twig',
    LiteralBlockNode::class => 'body/literal-block.tex.twig',
    CitationNode::class => 'body/citation.tex.twig',
    FootnoteNode::class => 'body/footnote.tex.twig',
    AnnotationListNode::class => 'body/annotation-list.tex.twig',
    // Inline
    ImageInlineNode::class => 'inline/image.tex.twig',
    InlineCompoundNode::class => 'inline/inline-node.tex.twig',
    AbbreviationInlineNode::class => 'inline/textroles/abbreviation.tex.twig',
    CitationInlineNode::class => 'inline/citation.tex.twig',
    DocReferenceNode::class => 'inline/doc.tex.twig',
    EmphasisInlineNode::class => 'inline/emphasis.tex.twig',
    FootnoteInlineNode::class => 'inline/footnote.tex.twig',
    HyperLinkNode::class => 'inline/link.tex.twig',
    LiteralInlineNode::class => 'inline/literal.tex.twig',
    NewlineInlineNode::class => 'inline/newline.tex.twig',
    WhitespaceInlineNode::class => 'inline/nbsp.tex.twig',
    PlainTextInlineNode::class => 'inline/plain-text.tex.twig',
    ReferenceNode::class => 'inline/ref.tex.twig',
    StrongInlineNode::class => 'inline/strong.tex.twig',
    VariableInlineNode::class => 'inline/variable.tex.twig',
    GenericTextRoleInlineNode::class => 'inline/textroles/generic.tex.twig',
    // Output as Metatags
    AuthorNode::class => 'structure/header/author.tex.twig',
    CopyrightNode::class => 'structure/header/copyright.tex.twig',
    DateNode::class => 'structure/header/date.tex.twig',
    NoSearchNode::class => 'structure/header/no-search.tex.twig',
    TopicNode::class => 'structure/header/topic.tex.twig',
    // No output in page header in tex - might be output in i.e. LaTex
    AddressNode::class => 'structure/header/blank.tex.twig',
    AuthorsNode::class => 'structure/header/blank.tex.twig',
    ContactNode::class => 'structure/header/blank.tex.twig',
    NoCommentsNode::class => 'structure/header/blank.tex.twig',
    OrganizationNode::class => 'structure/header/blank.tex.twig',
    OrphanNode::class => 'structure/header/blank.tex.twig',
    RevisionNode::class => 'structure/header/blank.tex.twig',
    TocDepthNode::class => 'structure/header/blank.tex.twig',
    VersionNode::class => 'structure/header/blank.tex.twig',
];
