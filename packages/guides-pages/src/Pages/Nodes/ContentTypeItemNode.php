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

use DateTimeImmutable;
use phpDocumentor\Guides\Nodes\AbstractNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Metadata\DateNode;
use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Pages\Nodes\Metadata\ContentTypeTemplateNode;
use phpDocumentor\Guides\Pages\Nodes\Metadata\PageDestinationNode;

use function is_string;
use function ltrim;

/**
 * Root AST node for a single item inside a content-type collection.
 *
 * A content-type item is authored in any supported documentation format
 * (RST, Markdown, …), parsed from a dedicated collection source directory,
 * compiled, and rendered as a self-contained HTML page. Like {@see PageNode},
 * items are intentionally **not** part of the documentation tree.
 *
 * In addition to the common {@see RenderablePageInterface} contract, a
 * content-type item carries:
 *
 * - A **publication date** read from the `:date:` field-list entry
 *   ({@see DateNode}).
 * - An optional **per-item template override** read from the `:page-template:`
 *   field-list entry ({@see ContentTypeTemplateNode}). When present it takes
 *   precedence over the collection-level `item-template` configured in
 *   `guides.xml`.
 * - An auto-extracted **summary** — the first {@see ParagraphNode} found in
 *   the document body (descending into the first {@see SectionNode} if the
 *   body starts with one), returned as a node so templates can render it
 *   directly via `renderNode(item.summary)`.
 *
 * @extends AbstractNode<Node[]>
 */
final class ContentTypeItemNode extends AbstractNode implements RenderablePageInterface
{
    /**
     * Where this item should be written relative to the output root.
     * Does not include the file extension (e.g. "news/2026-01-15-launch").
     */
    private string $outputPath;

    /**
     * Nodes rendered in the HTML <head> (metadata), excluding
     * {@see PageDestinationNode}, {@see DateNode}, and
     * {@see ContentTypeTemplateNode} which are promoted to dedicated fields.
     *
     * @var MetadataNode[]
     */
    private array $headerNodes = [];

    /** Publication date, or null when no `:date:` field was present. */
    private DateTimeImmutable|null $date = null;

    /**
     * Per-item Twig template path, or null when no `:page-template:` field
     * was present (the collection default applies in that case).
     */
    private string|null $itemTemplate = null;

    /**
     * The first paragraph node from the document body, or null when
     * no paragraph was found.
     */
    private ParagraphNode|null $summary = null;

    /**
     * @param Node[]      $children  Parsed body nodes (sections, paragraphs, …)
     * @param string      $filePath  Source file path relative to the collection
     *                               source directory, without extension
     */
    public function __construct(
        private readonly string $filePath,
        array $children = [],
    ) {
        $this->value    = $children;
        $this->outputPath = $filePath;
    }

    /**
     * Promotes a compiled {@see DocumentNode} into a {@see ContentTypeItemNode}.
     *
     * Extracts {@see DateNode}, {@see ContentTypeTemplateNode}, and
     * {@see PageDestinationNode} from the document's header nodes and finds
     * the first {@see ParagraphNode} in the body as the summary.
     */
    public static function from(DocumentNode $documentNode): self
    {
        $node = new self($documentNode->getFilePath(), $documentNode->getChildren());
        $node->setOutputPath($documentNode->getFilePath());

        foreach ($documentNode->getHeaderNodes() as $headerNode) {
            if ($headerNode instanceof PageDestinationNode) {
                $node->setOutputPath($headerNode->getDestination());
                continue;
            }

            if ($headerNode instanceof DateNode) {
                $node->date = self::parseDateValue($headerNode->getValue());
                continue;
            }

            if ($headerNode instanceof ContentTypeTemplateNode) {
                $node->itemTemplate = $headerNode->getTemplatePath() ?: null;
                continue;
            }

            $node->addHeaderNode($headerNode);
        }

        $node->summary = self::extractSummary($documentNode->getChildren());

        return $node;
    }

    /**
     * Returns a copy of this item with `$sourceDir` prepended to both the
     * {@see getFilePath()} and {@see getOutputPath()} values.
     *
     * This is needed because {@see \phpDocumentor\Guides\Handlers\ParseFileCommand}
     * returns a {@see DocumentNode} whose `filePath` is relative to the
     * collection `$sourceDir` rather than to the output root. Calling this
     * method after {@see from()} ensures that items render to the correct
     * output subdirectory (e.g. `news/2026-01-release` instead of just
     * `2026-01-release`).
     *
     * A custom {@see PageDestinationNode} override is preserved unchanged when
     * it was explicitly set (i.e. `outputPath !== filePath`).
     */
    public function withSourceDirectory(string $sourceDir): self
    {
        $prefix = ltrim($sourceDir, '/');
        if ($prefix === '') {
            return $this;
        }

        $hadCustomOutputPath = $this->outputPath !== $this->filePath;

        $clone = new self($prefix . '/' . $this->filePath, $this->value);
        $clone->headerNodes  = $this->headerNodes;
        $clone->date         = $this->date;
        $clone->itemTemplate = $this->itemTemplate;
        $clone->summary      = $this->summary;
        $clone->outputPath   = $hadCustomOutputPath
            ? $this->outputPath          // keep explicit PageDestinationNode override as-is
            : $prefix . '/' . $this->outputPath;

        return $clone;
    }

    /**
     * Returns a copy of this item with `$itemTemplate` set as the item template.
     *
     * Used by {@see \phpDocumentor\Guides\Pages\EventListener\ParseContentTypeListener}
     * to stamp the collection-level default template onto items that do not
     * carry their own per-item `:page-template:` override.
     */
    public function withItemTemplate(string $itemTemplate): self
    {
        $clone               = clone $this;
        $clone->itemTemplate = $itemTemplate !== '' ? $itemTemplate : null;

        return $clone;
    }

    /**
     * Wraps this item's body children in a temporary {@see DocumentNode} so
     * that the standard compiler can process cross-references.
     */
    public function toDocument(): DocumentNode
    {
        $documentNode = (new DocumentNode($this->filePath, $this->filePath))
            ->withIsRoot(true)
            ->setOrphan(true);

        foreach ($this->headerNodes as $headerNode) {
            $documentNode->addHeaderNode($headerNode);
        }

        if ($this->date !== null) {
            $documentNode->addHeaderNode(new DateNode($this->date->format('Y-m-d')));
        }

        if ($this->itemTemplate !== null) {
            $documentNode->addHeaderNode(new ContentTypeTemplateNode($this->itemTemplate));
        }

        foreach ($this->getChildren() as $child) {
            $documentNode->addChildNode($child);
        }

        return $documentNode;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

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
        return $this->headerNodes;
    }

    /**
     * Returns the plain-text page title for use in the HTML `<title>` element.
     *
     * The title is extracted from the first {@see TitleNode} found by walking
     * the body children (descending into the first {@see SectionNode} when the
     * body opens with one). This is the RST document/section heading.
     *
     * Falls back to `null` when no title node is found in the body.
     */
    public function getPageTitle(): string|null
    {
        return self::extractTitle($this->value);
    }

    /**
     * Walks top-level body nodes looking for the first {@see TitleNode},
     * descending into the first {@see SectionNode} when necessary.
     *
     * @param Node[] $children
     */
    private static function extractTitle(array $children): string|null
    {
        foreach ($children as $child) {
            if ($child instanceof TitleNode) {
                $title = $child->toString();

                return $title !== '' ? $title : null;
            }

            if ($child instanceof SectionNode) {
                return self::extractTitle($child->getChildren());
            }
        }

        return null;
    }

    /**
     * Publication date parsed from the `:date:` field-list entry, or `null`
     * when none was declared or the value could not be parsed as `Y-m-d`.
     */
    public function getDate(): DateTimeImmutable|null
    {
        return $this->date;
    }

    /**
     * Per-item Twig template path override from `:page-template:`, or `null`
     * when the item does not override the collection default.
     */
    public function getItemTemplate(): string|null
    {
        return $this->itemTemplate;
    }

    /**
     * The first paragraph node auto-extracted from the body, or `null` when
     * no paragraph node was found. Templates should render this via
     * `renderNode(item.summary)`.
     */
    public function getSummary(): ParagraphNode|null
    {
        return $this->summary;
    }

    /**
     * Parses a raw date string (expected format `Y-m-d`) into a
     * {@see DateTimeImmutable}, returning `null` for empty or non-conforming
     * strings (e.g. `"not-a-date"`, `"2026-01"`).
     */
    private static function parseDateValue(string|null $raw): DateTimeImmutable|null
    {
        if (!is_string($raw) || $raw === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $raw);

        return $date instanceof DateTimeImmutable ? $date : null;
    }

    /**
     * Walks the top-level child nodes and returns the first
     * {@see ParagraphNode} encountered, descending into the first
     * {@see SectionNode} when the body opens with one.
     *
     * @param Node[] $children
     */
    private static function extractSummary(array $children): ParagraphNode|null
    {
        foreach ($children as $child) {
            if ($child instanceof SectionNode) {
                return self::extractSummary($child->getChildren());
            }

            if ($child instanceof ParagraphNode) {
                return $child;
            }
        }

        return null;
    }
}
