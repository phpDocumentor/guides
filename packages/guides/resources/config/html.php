<?php

declare(strict_types=1);
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\FigureNode;
use phpDocumentor\Guides\Nodes\Metadata\MetaNode;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\QuoteNode;
use phpDocumentor\Guides\Nodes\SeparatorNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Nodes\SectionBeginNode;
use phpDocumentor\Guides\Nodes\SectionEndNode;
use phpDocumentor\Guides\Nodes\ImageNode;
use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\DefinitionListNode;
use phpDocumentor\Guides\Nodes\ListNode;
use phpDocumentor\Guides\Nodes\LiteralBlockNode;

use phpDocumentor\Guides\Nodes;

return [
    AnchorNode::class => 'anchor.html.twig',
    FigureNode::class => 'figure.html.twig',
    MetaNode::class => 'meta.html.twig',
    ParagraphNode::class => 'paragraph.html.twig',
    QuoteNode::class => 'quote.html.twig',
    SeparatorNode::class => 'separator.html.twig',
    TitleNode::class => 'header-title.html.twig',
    SectionNode::class => 'section.html.twig',
    SectionBeginNode::class => 'section-begin.html.twig',
    SectionEndNode::class => 'section-end.html.twig',
    ImageNode::class => 'image.html.twig',
    CodeNode::class => 'code.html.twig',
    DefinitionListNode::class => 'definition-list.html.twig',
    ListNode::class => 'list.html.twig',
    LiteralBlockNode::class => 'directives/literal-block.html.twig'
];
