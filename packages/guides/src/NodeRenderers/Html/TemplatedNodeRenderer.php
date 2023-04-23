<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\NodeRenderers\Html;

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TemplatedNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;
use Webmozart\Assert\Assert;

/** @implements NodeRenderer<TemplatedNode> */
final class TemplatedNodeRenderer implements NodeRenderer
{
    public function __construct(private readonly TemplateRenderer $renderer)
    {
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        Assert::isInstanceOf($node, TemplatedNode::class);

        return $this->renderer->renderTemplate($renderContext, $node->getValue(), $node->getData());
    }

    public function supports(Node $node): bool
    {
        return $node instanceof TemplatedNode;
    }
}
