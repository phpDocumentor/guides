<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\NodeRenderers\Html;

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;
use phpDocumentor\Guides\TemplateRenderer;

use function assert;

/** @implements NodeRenderer<MenuEntryNode> */
final class MenuEntryRenderer implements NodeRenderer
{
    public function __construct(
        private readonly TemplateRenderer $renderer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function supports(Node $node): bool
    {
        return $node instanceof MenuEntryNode;
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        assert($node instanceof MenuEntryNode);

        return $this->renderer->renderTemplate(
            $renderContext,
            'body/menu/menu-item.html.twig',
            [
                'url' => $this->urlGenerator->generateCanonicalOutputUrl($renderContext, $node->getUrl(), !$node->isDocumentRoot() ? $node->getValue()->getId() : null),
                'node' => $node,
            ],
        );
    }
}
