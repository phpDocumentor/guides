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

use phpDocumentor\Guides\Nodes\AbstractNode;
use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;
use phpDocumentor\Guides\Nodes\Node;

/**
 * Synthetic node representing the auto-generated overview/listing page for a
 * content-type collection (e.g. a "News" index listing all news items).
 *
 * One {@see ContentTypeOverviewNode} is created per configured collection in
 * `guides.xml` by {@see \phpDocumentor\Guides\Pages\EventListener\RenderPagesListener}
 * during the post-render phase, after all individual items have been sorted.
 *
 * This node implements {@see RenderablePageInterface} so that it flows through
 * the same {@see \phpDocumentor\Guides\NodeRenderers\DelegatingNodeRenderer}
 * pipeline as {@see PageNode} and {@see ContentTypeItemNode}.
 *
 * @extends AbstractNode<ContentTypeItemNode[]>
 */
final class ContentTypeOverviewNode extends AbstractNode implements RenderablePageInterface
{
    /**
     * @param ContentTypeItemNode[] $items   Sorted collection items (newest-first).
     * @param string $outputPath             Output path relative to the output root, without extension.
     * @param string $title                  Human-readable title for the overview page.
     * @param string $template               Twig template path for this overview.
     */
    public function __construct(
        private readonly string $outputPath,
        private readonly string $title,
        private readonly string $template,
        array $items = [],
    ) {
        $this->value = $items;
    }

    public function getFilePath(): string
    {
        return $this->outputPath;
    }

    public function getOutputPath(): string
    {
        return $this->outputPath;
    }

    public function getPageTitle(): string|null
    {
        return $this->title !== '' ? $this->title : null;
    }

    /** @return MetadataNode[] */
    public function getHeaderNodes(): array
    {
        return [];
    }

    /** @return Node[] */
    public function getChildren(): array
    {
        return [];
    }

    /**
     * Twig template path used to render this overview page.
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * The sorted list of {@see ContentTypeItemNode}s to display in the overview.
     *
     * @return ContentTypeItemNode[]
     */
    public function getItems(): array
    {
        return $this->value;
    }
}
