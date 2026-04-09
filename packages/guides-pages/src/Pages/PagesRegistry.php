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

namespace phpDocumentor\Guides\Pages;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Pages\Nodes\PageNode;

/**
 * Shared state carrier between {@see ParsePagesListener} and {@see RenderPagesListener}.
 *
 * After parsing is complete, {@see ParsePagesListener} stores the fully-parsed and
 * compiled {@see PageNode}s here. {@see RenderPagesListener} reads them back during
 * the post-render phase to write the final HTML output.
 *
 * This service must be registered as a shared singleton in the DI container so that
 * both listeners operate on the same instance.
 */
final class PagesRegistry
{
    /** @var PageNode[] */
    private array $pages = [];

    public function addPage(PageNode $page): void
    {
        $this->pages[$page->getFilePath()] = $page;
    }

    /** @return PageNode[] */
    public function getPages(): array
    {
        return $this->pages;
    }

    /** @param DocumentNode[] $documents */
    public function updatePages(array $documents): void
    {
        foreach ($documents as $document) {
            $this->pages[$document->getFilePath()] = PageNode::from($document);
        }
    }
}
