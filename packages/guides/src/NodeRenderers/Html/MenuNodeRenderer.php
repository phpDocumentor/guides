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

namespace phpDocumentor\Guides\NodeRenderers\Html;

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Menu\ContentMenuNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;
use Webmozart\Assert\Assert;

use function is_a;

/** @implements NodeRenderer<MenuNode> */
final class MenuNodeRenderer implements NodeRenderer
{
    public function __construct(private readonly TemplateRenderer $renderer)
    {
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        Assert::isInstanceOf($node, MenuNode::class);

        if ($node->getOption('hidden', false)) {
            return '';
        }

        return $this->renderer->renderTemplate(
            $renderContext,
            $this->getTemplate($node),
            ['node' => $node],
        );
    }

    private function getTemplate(Node $node): string
    {
        if ($node instanceof TocNode) {
            return 'body/menu/table-of-content.html.twig';
        }

        if ($node instanceof ContentMenuNode) {
            return 'body/menu/content-menu.html.twig';
        }

        return 'body/menu/menu.html.twig';
    }

    public function supports(string $nodeFqcn): bool
    {
        return $nodeFqcn === MenuNode::class || is_a($nodeFqcn, MenuNode::class, true);
    }
}
