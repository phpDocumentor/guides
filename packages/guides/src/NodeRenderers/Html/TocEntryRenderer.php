<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\NodeRenderers\Html;

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TableOfContents\Entry;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer;

/** @implements NodeRenderer<Entry> */
final class TocEntryRenderer implements NodeRenderer
{
    private Renderer $renderer;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof Entry;
    }

    public function render(Node $node, RenderContext $environment): string
    {
        return $this->renderer->render(
            'body/toc/toc-item.html.twig',
            [
                'url' => $environment->relativeDocUrl($node->getUrl(), $node->getValue()->getId()),
                'node' => $node
            ]
        );
    }
}
