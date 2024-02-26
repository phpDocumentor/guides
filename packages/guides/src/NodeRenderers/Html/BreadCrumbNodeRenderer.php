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
use phpDocumentor\Guides\Nodes\BreadCrumbNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\Menu\InternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;

use function array_reverse;
use function assert;
use function is_a;

/**
 * @template T as Node
 * @implements NodeRenderer<BreadCrumbNode>
 */
final class BreadCrumbNodeRenderer implements NodeRenderer
{
    private string $template = 'body/menu/breadcrumb.html.twig';

    public function __construct(
        private readonly TemplateRenderer $renderer,
    ) {
    }

    public function supports(string $nodeFqcn): bool
    {
        return $nodeFqcn === BreadCrumbNode::class || is_a($nodeFqcn, BreadCrumbNode::class, true);
    }
    
    /** @param T $node */
    public function render(Node $node, RenderContext $renderContext): string
    {
        assert($node instanceof BreadCrumbNode);
        $documentEntry = $renderContext->getCurrentDocumentEntry();
        $data = [
            'node' => $node,
            'rootline' => $documentEntry === null ? [] :
                $this->buildBreadcrumb(
                    $node,
                    $renderContext,
                    $documentEntry,
                    [],
                    $this->getBreadcrumbMaxLevel($node, $renderContext, $documentEntry, 0),
                    true,
                ),
        ];

        return $this->renderer->renderTemplate(
            $renderContext,
            $this->template,
            $data,
        );
    }
    
    private function getBreadcrumbMaxLevel(
        BreadCrumbNode $node,
        RenderContext $renderContext,
        DocumentEntryNode $documentEntry,
        int $level,
    ): int {
        if ($documentEntry->getParent() === null) {
            if ($documentEntry->isRoot()) {
                return $level;
            }

            // Current document has no parent but is not the root, add the overall root to the breadcrumb
            $entries = $renderContext->getProjectNode()->getAllDocumentEntries();
            foreach ($entries as $entry) {
                if ($entry->isRoot() && $entry->getParent() === null) {
                    return $this->getBreadcrumbMaxLevel($node, $renderContext, $entry, ++$level);
                }
            }

            return $level;
        }

        return $this->getBreadcrumbMaxLevel($node, $renderContext, $documentEntry->getParent(), ++$level);
    }

    /**
     * @param MenuEntryNode[] $currentBreadcrumb
     *
     * @return MenuEntryNode[]
     */
    private function buildBreadcrumb(
        BreadCrumbNode $node,
        RenderContext $renderContext,
        DocumentEntryNode $documentEntry,
        array $currentBreadcrumb,
        int $level,
        bool $isCurrent,
    ): array {
        $title = $documentEntry->getTitle();
        $navigationTitle =  $documentEntry->getAdditionalData('navigationTitle');
        if ($navigationTitle instanceof TitleNode) {
            $title = $navigationTitle;
        }

        $entry = new InternalMenuEntryNode(
            $documentEntry->getFile(),
            $title,
            [],
            false,
            $level,
            '',
            true,
            $isCurrent,
        );
        $currentBreadcrumb[] = $entry;
        if ($documentEntry->getParent() === null) {
            if ($documentEntry->isRoot()) {
                return array_reverse($currentBreadcrumb);
            }

            // Current document has no parent but is not the root, add the overall root to the breadcrumb
            $entries = $renderContext->getProjectNode()->getAllDocumentEntries();
            foreach ($entries as $entry) {
                if ($entry->isRoot() && $entry->getParent() === null) {
                    return $this->buildBreadcrumb($node, $renderContext, $entry, $currentBreadcrumb, --$level, false);
                }
            }

            return array_reverse($currentBreadcrumb);
        }

        return $this->buildBreadcrumb($node, $renderContext, $documentEntry->getParent(), $currentBreadcrumb, --$level, false);
    }
}
