<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\NodeRenderers\Html\Metadata;

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Metadata\DocumentTitleNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer;

/** @implements NodeRenderer<DocumentTitleNode> */
final class DocumentTitleNodeRenderer implements NodeRenderer
{
    private Renderer $renderer;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof DocumentTitleNode;
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        return $this->renderer->render('page/header/title.html.twig', ['title' => $node->getValue()]);
    }
}
