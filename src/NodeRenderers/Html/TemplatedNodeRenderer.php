<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\NodeRenderers\Html;

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TemplatedNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer;
use Webmozart\Assert\Assert;

final class TemplatedNodeRenderer implements NodeRenderer
{
    /** @var Renderer */
    private $renderer;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render(Node $node, RenderContext $environment): string
    {
        Assert::isInstanceOf($node, TemplatedNode::class);

        return $this->renderer->render($node->getValue(), $node->getData());
    }

    public function supports(Node $node): bool
    {
        return $node instanceof TemplatedNode;
    }
}
