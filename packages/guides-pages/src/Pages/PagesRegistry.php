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
use phpDocumentor\Guides\Pages\Nodes\ContentTypeItemNode;
use phpDocumentor\Guides\Pages\Nodes\ContentTypeOverviewNode;
use phpDocumentor\Guides\Pages\Nodes\PageNode;
use phpDocumentor\Guides\Pages\Nodes\RenderablePageInterface;

use function array_merge;
use function array_values;
use function usort;

/**
 * Shared state carrier between the parse and render event listeners.
 *
 * Stores two separate collections:
 *
 * - **Static pages** — {@see PageNode} instances discovered by
 *   {@see \phpDocumentor\Guides\Pages\EventListener\ParsePagesListener}.
 * - **Content-type items** — {@see ContentTypeItemNode} instances discovered by
 *   {@see \phpDocumentor\Guides\Pages\EventListener\ParseContentTypeListener},
 *   grouped by the collection key (= `source_directory` value from `guides.xml`).
 *
 * {@see RenderPagesListener} reads both collections back during the post-render
 * phase via {@see getAllRenderables()} to write the final HTML output.
 *
 * This service must be registered as a **shared singleton** in the DI container
 * so that all listeners operate on the same instance.
 */
final class PagesRegistry
{
    /** @var PageNode[] keyed by filePath */
    private array $pages = [];

    /**
     * Content-type items grouped by collection key (source_directory).
     *
     * @var array<string, ContentTypeItemNode[]>
     */
    private array $collectionItems = [];

    /**
     * Overview nodes keyed by outputPath.
     *
     * @var array<string, ContentTypeOverviewNode>
     */
    private array $overviews = [];

    // -------------------------------------------------------------------------
    // Static pages
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // Content-type collection items
    // -------------------------------------------------------------------------

    public function addCollectionItem(string $collectionKey, ContentTypeItemNode $item): void
    {
        $this->collectionItems[$collectionKey][$item->getFilePath()] = $item;
    }

    /**
     * Returns all items for a collection in their original (parse) order.
     *
     * @return ContentTypeItemNode[]
     */
    public function getCollectionItems(string $collectionKey): array
    {
        return array_values($this->collectionItems[$collectionKey] ?? []);
    }

    /**
     * Returns all items for a collection sorted **newest-first** by publication
     * date. Items without a date sort after all dated items, preserving their
     * relative parse order among themselves.
     *
     * @return ContentTypeItemNode[]
     */
    public function getSortedCollectionItems(string $collectionKey): array
    {
        $items = $this->getCollectionItems($collectionKey);

        usort($items, static function (ContentTypeItemNode $a, ContentTypeItemNode $b): int {
            $dateA = $a->getDate();
            $dateB = $b->getDate();

            if ($dateA === null && $dateB === null) {
                return 0;
            }

            if ($dateA === null) {
                return 1; // undated items sort after dated items
            }

            if ($dateB === null) {
                return -1; // dated items sort before undated items
            }

            return $dateB <=> $dateA; // newest first
        });

        return $items;
    }

    /** @param DocumentNode[] $documents */
    public function updateCollectionItems(string $collectionKey, array $documents): void
    {
        foreach ($documents as $document) {
            $filePath = $document->getFilePath();
            $this->collectionItems[$collectionKey][$filePath] = ContentTypeItemNode::from($document);
        }
    }

    // -------------------------------------------------------------------------
    // Overview nodes
    // -------------------------------------------------------------------------

    public function addOverview(ContentTypeOverviewNode $overview): void
    {
        $this->overviews[$overview->getOutputPath()] = $overview;
    }

    // -------------------------------------------------------------------------
    // Combined access for the render listener
    // -------------------------------------------------------------------------

    /**
     * Returns all static pages **and** all content-type items as a flat list
     * of {@see RenderablePageInterface} instances.
     *
     * Used by {@see \phpDocumentor\Guides\Pages\EventListener\RenderPagesListener}
     * to render everything in a single loop.
     *
     * @return RenderablePageInterface[]
     */
    public function getAllRenderables(): array
    {
        $allItems = [];
        foreach ($this->collectionItems as $items) {
            $allItems = array_merge($allItems, array_values($items));
        }

        return array_merge(array_values($this->pages), $allItems, array_values($this->overviews));
    }
}
