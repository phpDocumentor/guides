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
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Pages\Nodes\Metadata\PageDestinationNode;

use function array_filter;
use function array_values;

/**
 * Root AST node for a standalone page.
 *
 * A page is authored in any supported documentation format (RST, Markdown, …),
 * parsed from a dedicated source directory, compiled, and rendered as a
 * self-contained HTML page. Pages are intentionally **not** part of the
 * documentation tree: they have no toctree membership, no
 * {@see \phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode} in the
 * {@see \phpDocumentor\Guides\Nodes\ProjectNode}, and no menu entries.
 *
 * The **output path** for each page is declared inside the source file via a
 * format-specific emitter of {@see PageDestinationNode}. If no destination is
 * declared the source file path (without extension) is used as the fallback.
 *
 * @extends AbstractNode<Node[]>
 */
final class PageNode extends AbstractNode
{
    /**
     * Where this page should be written relative to the output root.
     * Does not include the file extension (e.g. "about/index").
     */
    private string $outputPath;

    /**
     * Nodes that are rendered in the HTML <head> (metadata).
     *
     * @var MetadataNode[]
     */
    private array $headerNodes = [];

    /**
     * @param Node[]      $children   Parsed body nodes (sections, paragraphs, …)
     * @param string      $filePath   Source file path relative to the pages source directory,
     *                                without extension (e.g. "about/index")
     */
    public function __construct(
        private readonly string $filePath,
        array $children = [],
    ) {
        $this->value = $children;
        // Default output path equals the source file path; overridden by PageDestinationNode
        $this->outputPath = $filePath;
    }

    public static function from(DocumentNode $documentNode): self
    {
        $pageNode = new self($documentNode->getFilePath(), $documentNode->getChildren());
        $pageNode->setOutputPath($documentNode->getFilePath());

        foreach ($documentNode->getHeaderNodes() as $headerNode) {
            $pageNode->addHeaderNode($headerNode);
            if ($headerNode instanceof PageDestinationNode === false) {
                continue;
            }

            $pageNode->setOutputPath($headerNode->getDestination());
        }

        return $pageNode;
    }

    public function toDocument(): DocumentNode
    {
        $documentNode = (new DocumentNode($this->filePath, $this->filePath))
            ->withIsRoot(true)
            ->setOrphan(true);

        foreach ($this->headerNodes as $headerNode) {
            $documentNode->addHeaderNode($headerNode);
        }

        foreach ($this->getChildren() as $child) {
            $documentNode->addChildNode($child);
        }

        return $documentNode;
    }

    /**
     * Source file path relative to the pages source directory, without extension.
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * Output path relative to the output root, without extension.
     * Determined from {@see PageDestinationNode} if present, otherwise equals {@see getFilePath()}.
     */
    public function getOutputPath(): string
    {
        return $this->outputPath;
    }

    public function setOutputPath(string $outputPath): void
    {
        $this->outputPath = $outputPath;
    }

    /** @return Node[] */
    public function getChildren(): array
    {
        return $this->value;
    }

    public function addHeaderNode(MetadataNode $node): void
    {
        $this->headerNodes[] = $node;
    }

    /** @return MetadataNode[] */
    public function getHeaderNodes(): array
    {
        return array_values(array_filter(
            $this->headerNodes,
            static fn (MetadataNode $node): bool => !$node instanceof PageDestinationNode,
        ));
    }

    /**
     * Returns the title string for use in the HTML <title> element, derived from
     * the first {@see PageDestinationNode} or the first section title found in the
     * header nodes.
     */
    public function getPageTitle(): string|null
    {
        foreach ($this->headerNodes as $headerNode) {
            if ($headerNode instanceof PageDestinationNode) {
                continue;
            }

            $title = $headerNode->toString();
            if ($title !== '') {
                return $title;
            }
        }

        return null;
    }

    /**
     * @param class-string<F> $nodeType
     *
     * @return F[]
     *
     * @template F as Node
     */
    public function getNodes(string $nodeType = Node::class): array
    {
        /** @var F[] $filtered */
        $filtered = array_values(array_filter(
            $this->value,
            static fn (Node $node): bool => $node instanceof $nodeType,
        ));

        return $filtered;
    }
}
