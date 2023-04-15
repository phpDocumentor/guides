<?php

declare(strict_types=1);

use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\DefinitionListNode;
use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\FieldListNode;
use phpDocumentor\Guides\Nodes\FieldLists\FieldNode;
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

return [
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
    FieldNode::class => 'body/field.html.twig',
    ListNode::class => 'body/list/list.html.twig',
    ListItemNode::class => 'body/list/list-item.html.twig',
    LiteralBlockNode::class => 'body/literal-block.html.twig',
];
