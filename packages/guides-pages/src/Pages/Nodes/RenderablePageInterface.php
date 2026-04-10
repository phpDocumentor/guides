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

namespace phpDocumentor\Guides\Pages\Nodes;

use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;
use phpDocumentor\Guides\Nodes\Node;

/**
 * Common contract for nodes that are rendered as standalone HTML pages outside
 * the main documentation tree.
 *
 * Both {@see PageNode} (static pages) and {@see ContentTypeItemNode} (content-type
 * collection items) implement this interface so that {@see \phpDocumentor\Guides\Pages\EventListener\RenderPagesListener}
 * can iterate over all renderables uniformly.
 */
interface RenderablePageInterface
{
    /**
     * Source file path relative to its source directory, without extension.
     * Used to build a thin {@see \phpDocumentor\Guides\Nodes\DocumentNode} for
     * {@see \phpDocumentor\Guides\RenderContext} compatibility.
     */
    public function getFilePath(): string;

    /**
     * Output path relative to the output root, without extension.
     * This is the path at which the rendered HTML file is written.
     */
    public function getOutputPath(): string;

    /**
     * Title string for the HTML <title> element. May be null when no title
     * metadata is present in the source file.
     */
    public function getPageTitle(): string|null;

    /**
     * Metadata nodes that should be rendered inside the HTML <head>.
     *
     * @return MetadataNode[]
     */
    public function getHeaderNodes(): array;

    /**
     * Body nodes that make up the visible page content.
     *
     * @return Node[]
     */
    public function getChildren(): array;
}
