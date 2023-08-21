<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\NodeRenderers\Html;

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\BreadCrumbNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;

use function array_reverse;
use function assert;

/**
 * @template T as Node
 * @implements NodeRenderer<BreadCrumbNode>
 */
class BreadCrumbNodeRenderer implements NodeRenderer
{
    private string $template = 'body/menu/breadcrumb.html.twig';

    public function __construct(
        private readonly TemplateRenderer $renderer,
    ) {
    }

    public function supports(Node $node): bool
    {
        return $node instanceof BreadCrumbNode;
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
        $entry = new MenuEntryNode(
            $documentEntry->getFile(),
            $documentEntry->getTitle(),
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
