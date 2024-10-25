<?php

declare(strict_types=1);

use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\AnnotationListNode;
use phpDocumentor\Guides\Nodes\CitationNode;
use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\Configuration\ConfigurationBlockNode;
use phpDocumentor\Guides\Nodes\DefinitionListNode;
use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\EmbeddedFrame;
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
use phpDocumentor\Guides\Nodes\MathNode;
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
    AnchorNode::class => 'inline/anchor.html.twig',
    \phpDocumentor\Guides\Nodes\AuthorNode::class => 'body/author.html.twig',
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
    ConfigurationBlockNode::class => 'body/configuration-block.html.twig',
    DefinitionListNode::class => 'body/definition-list.html.twig',
    DefinitionNode::class => 'body/definition.html.twig',
    FieldListNode::class => 'body/field-list.html.twig',
    ListNode::class => 'body/list/list.html.twig',
    ListItemNode::class => 'body/list/list-item.html.twig',
    LiteralBlockNode::class => 'body/literal-block.html.twig',
    MathNode::class => 'body/math.html.twig',
    CitationNode::class => 'body/citation.html.twig',
    FootnoteNode::class => 'body/footnote.html.twig',
    AnnotationListNode::class => 'body/annotation-list.html.twig',
    EmbeddedFrame::class => 'body/embedded-frame.html.twig',
    // Inline
    ImageInlineNode::class => 'inline/image.html.twig',
    AbbreviationInlineNode::class => 'inline/textroles/abbreviation.html.twig',
    CitationInlineNode::class => 'inline/citation.html.twig',
    DocReferenceNode::class => 'inline/doc.html.twig',
    EmphasisInlineNode::class => 'inline/emphasis.html.twig',
    FootnoteInlineNode::class => 'inline/footnote.html.twig',
    HyperLinkNode::class => 'inline/link.html.twig',
    LiteralInlineNode::class => 'inline/literal.html.twig',
    NewlineInlineNode::class => 'inline/newline.html.twig',
    WhitespaceInlineNode::class => 'inline/nbsp.html.twig',
    PlainTextInlineNode::class => 'inline/plain-text.html.twig',
    ReferenceNode::class => 'inline/ref.html.twig',
    StrongInlineNode::class => 'inline/strong.html.twig',
    VariableInlineNode::class => 'inline/variable.html.twig',
    GenericTextRoleInlineNode::class => 'inline/textroles/generic.html.twig',
    InlineCompoundNode::class => 'inline/inline-node.html.twig',

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
