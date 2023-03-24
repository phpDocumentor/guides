<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\NodeRenderers;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TemplatedNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer;

/**
 * @template T as Node
 * @implements NodeRenderer<T>
 */
final class TemplateNodeRenderer implements NodeRenderer
{
    private Renderer $renderer;

    private string $template;

    private string $nodeClass;

    /** @param class-string<T> $nodeClass */
    public function __construct(Renderer $renderer, string $template, string $nodeClass)
    {
        $this->renderer = $renderer;
        $this->template = $template;
        $this->nodeClass = $nodeClass;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof $this->nodeClass;
    }

    /** @param T $node */
    public function render(Node $node, RenderContext $renderContext): string
    {
        return $this->renderer->render(
            $this->template,
            ['node' => $node]
        );
    }
}
