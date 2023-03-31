<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\NodeRenderers\Html;

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TableOfContents\Entry;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;

/** @implements NodeRenderer<Entry> */
final class TocEntryRenderer implements NodeRenderer
{
    private TemplateRenderer $renderer;

    public function __construct(TemplateRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof Entry;
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        return $this->renderer->renderTemplate(
            $renderContext,
            'body/toc/toc-item.html.twig',
            [
                'url' => $renderContext->relativeDocUrl($node->getUrl(), $node->getValue()->getId()),
                'node' => $node,
            ]
        );
    }
}
