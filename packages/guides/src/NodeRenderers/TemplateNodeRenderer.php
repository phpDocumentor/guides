<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\NodeRenderers;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;

/**
 * @template T as Node
 * @implements NodeRenderer<T>
 */
final class TemplateNodeRenderer implements NodeRenderer
{
    /** @param class-string<T> $nodeClass */
    public function __construct(private TemplateRenderer $renderer, private string $template, private string $nodeClass)
    {
    }

    public function supports(Node $node): bool
    {
        return $node instanceof $this->nodeClass;
    }

    /** @param T $node */
    public function render(Node $node, RenderContext $renderContext): string
    {
        return $this->renderer->renderTemplate(
            $renderContext,
            $this->template,
            ['node' => $node],
        );
    }
}
