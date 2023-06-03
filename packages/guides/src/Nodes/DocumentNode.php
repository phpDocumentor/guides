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

namespace phpDocumentor\Guides\Nodes;

use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;

use function array_filter;
use function strtolower;
use function trim;

/** @extends CompoundNode<Node> */
final class DocumentNode extends CompoundNode
{
    /**
     * Header nodes are rendered in the head of a html page.
     * They contain metadata about the document.
     *
     * @var MetadataNode[]
     */
    private array $headerNodes = [];

    /**
     * Nodes that can be rendered in a special section of the page like the menu or the footer
     *
     * @var array<string, Node[]>
     */
    private array $documentPartNodes = [];

    /**
     * Variables are replacements in a document.
     *
     * They easiest example is the replace directive that allows textual replacements in the document. But
     * also other directives may be prefixed with a name to replace a certain value in the text.
     *
     * @var array<(string | Node)>
     */
    private array $variables = [];

    /** @var string[] */
    private array $links;

    private bool $titleFound = false;

    private string|null $metaTitle = null;

    public function __construct(
        private readonly string $hash,
        private readonly string $filePath,
    ) {
        parent::__construct([]);
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @param class-string<F> $nodeType
     *
     * @return array<int, F>
     *
     * @template F as Node
     */
    public function getNodes(string $nodeType = Node::class): array
    {
        return array_filter($this->value, static fn ($node): bool => $node instanceof $nodeType);
    }

    public function getPageTitle(): string|null
    {
        if ($this->metaTitle !== null) {
            return $this->metaTitle;
        }

        if ($this->getTitle() instanceof TitleNode) {
            return $this->getTitle()->toString();
        }

        return null;
    }

    public function getTitle(): TitleNode|null
    {
        foreach ($this->value as $node) {
            if ($node instanceof SectionNode && $node->getTitle()->getLevel() === 1) {
                return $node->getTitle();
            }
        }

        return null;
    }

    public function setMetaTitle(string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
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

    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param TType $default
     *
     * @phpstan-return TType|string|Node
     *
     * @template TType as mixed
     */
    public function getVariable(string $name, mixed $default): mixed
    {
        return $this->variables[$name] ?? $default;
    }

    public function addVariable(string $name, string|Node $value): void
    {
        $this->variables[$name] = $value;
    }

    /** @param array<string, string> $links */
    public function setLinks(array $links): void
    {
        $this->links = $links;
    }

    public function getLink(string $name): string|null
    {
        return $this->links[strtolower(trim($name))] ?? null;
    }

    public function isTitleFound(): bool
    {
        return $this->titleFound;
    }

    public function setTitleFound(bool $titleFound): void
    {
        $this->titleFound = $titleFound;
    }

    /** @return array<string, string> */
    public function getLoggerInformation(): array
    {
        return [
            'rst-file' => $this->getFilePath() . '.rst',
        ];
    }

    /** @return array<string, Node[]> */
    public function getDocumentPartNodes(): array
    {
        return $this->documentPartNodes;
    }

    /** @param Node[] $nodes */
    public function addDocumentPart(string $identifier, array $nodes): void
    {
        $this->documentPartNodes[$identifier] = $nodes;
    }

    public function hasDocumentPart(string $identifier): bool
    {
        return isset($this->documentPartNodes[$identifier]);
    }
}
