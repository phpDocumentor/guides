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

namespace phpDocumentor\Guides\RestructuredText\HTML;

use IteratorAggregate;
use phpDocumentor\Guides\NodeRenderers\DefaultNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\DefinitionListNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\DocumentNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\SpanNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\TableNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\TemplatedNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\TocNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\InMemoryNodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\NodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\TemplateNodeRenderer;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\DefinitionListNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\FigureNode;
use phpDocumentor\Guides\Nodes\ImageNode;
use phpDocumentor\Guides\Nodes\ListNode;
use phpDocumentor\Guides\Nodes\MetaNode;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\QuoteNode;
use phpDocumentor\Guides\Nodes\SectionBeginNode;
use phpDocumentor\Guides\Nodes\SectionEndNode;
use phpDocumentor\Guides\Nodes\SeparatorNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\Nodes\TemplatedNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Nodes\TocNode;
use phpDocumentor\Guides\Nodes\UmlNode;
use phpDocumentor\Guides\ReferenceBuilder;
use phpDocumentor\Guides\Renderer;
use phpDocumentor\Guides\RestructuredText\OutputFormat;

final class HTMLFormat extends OutputFormat
{
    /** @var NodeRendererFactory */
    private $nodeRendererFactory;

    public function __construct(
        Renderer $renderer,
        ReferenceBuilder $referenceBuilder,
        string $fileExtension,
        IteratorAggregate $directives
    ) {
        parent::__construct($fileExtension, $directives);

        $this->nodeRendererFactory = new InMemoryNodeRendererFactory(
            [
                AnchorNode::class => new TemplateNodeRenderer($renderer, 'anchor.html.twig'),
                FigureNode::class => new TemplateNodeRenderer($renderer, 'figure.html.twig'),
                MetaNode::class => new TemplateNodeRenderer($renderer, 'meta.html.twig'),
                ParagraphNode::class => new TemplateNodeRenderer($renderer, 'paragraph.html.twig'),
                QuoteNode::class => new TemplateNodeRenderer($renderer, 'quote.html.twig'),
                SeparatorNode::class => new TemplateNodeRenderer($renderer, 'separator.html.twig'),
                TitleNode::class => new TemplateNodeRenderer($renderer, 'header-title.html.twig'),
                SectionBeginNode::class => new TemplateNodeRenderer($renderer, 'section-begin.html.twig'),
                SectionEndNode::class => new TemplateNodeRenderer($renderer, 'section-end.html.twig'),
                ImageNode::class => new TemplateNodeRenderer($renderer, 'image.html.twig'),
                UmlNode::class => new TemplateNodeRenderer($renderer, 'uml.html.twig'),
                CodeNode::class => new TemplateNodeRenderer($renderer, 'code.html.twig'),
                DefinitionListNode::class => new DefinitionListNodeRenderer($renderer),
                ListNode::class => new TemplateNodeRenderer($renderer, 'list.html.twig'),
                TableNode::class => new TableNodeRenderer($renderer),
                TocNode::class => new TocNodeRenderer($renderer, $referenceBuilder),
                DocumentNode::class => new DocumentNodeRenderer(),
                SpanNode::class => new SpanNodeRenderer($renderer, $referenceBuilder),
                TemplatedNode::class => new TemplatedNodeRenderer($renderer),
            ],
            new DefaultNodeRenderer()
        );
    }

    public function getNodeRendererFactory(): NodeRendererFactory
    {
        return $this->nodeRendererFactory;
    }
}
