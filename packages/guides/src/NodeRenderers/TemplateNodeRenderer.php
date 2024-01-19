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

namespace phpDocumentor\Guides\NodeRenderers;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;

use function is_a;

/**
 * @template T as Node
 * @implements NodeRenderer<T>
 */
final class TemplateNodeRenderer implements NodeRenderer
{
    /** @param class-string<T> $nodeClass */
    public function __construct(private readonly TemplateRenderer $renderer, private readonly string $template, private readonly string $nodeClass)
    {
    }

    public function supports(string $nodeFqcn): bool
    {
        return $nodeFqcn === $this->nodeClass || is_a($nodeFqcn, $this->nodeClass, true);
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
