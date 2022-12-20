<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\NodeRenderers\Html;

use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TableOfContents\Entry;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer;
use phpDocumentor\Guides\UrlGeneratorInterface;

final class TocEntryRenderer implements NodeRenderer
{
    private Renderer $renderer;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        Renderer $renderer,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->renderer = $renderer;
        $this->urlGenerator = $urlGenerator;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof Entry;
    }

    public function render(Node $node, RenderContext $environment): string
    {
        return $this->renderer->render(
            'toc-item.html.twig',
            [
                'url' => $environment->relativeDocUrl($node->getUrl(), $node->getValue()->getId()),
                'node' => $node
            ]
        );
    }
}
