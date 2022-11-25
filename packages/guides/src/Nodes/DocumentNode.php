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
use function array_map;
use function array_merge;
use function count;
use function in_array;
use function is_string;
use function strtolower;
use function trim;

final class DocumentNode extends Node
{
    private string $hash;

    /**
     * Header nodes are rendered in the head of a html page.
     * They contain metadata about the document.
     *
     * @var MetadataNode[]
     */
    private array $headerNodes = [];

    /** @var Node[] */
    private array $nodes = [];

    /** @var string[] */
    private array $dependencies = [];

    /**
     * Variables are replacements in a document.
     *
     * They easiest example is the replace directive that allows textual replacements in the document. But
     * also other directives may be prefixed with a name to replace a certain value in the text.
     *
     * @var array<string|Node>
     */
    private array $variables = [];

    /** @var string Absolute file path of this document */
    private string $filePath;

    /** @var string[] */
    private array $links;

    public function __construct(string $value, string $filePath)
    {
        parent::__construct();

        $this->hash = $value;
        $this->filePath = $filePath;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @template F as Node
     * @param class-string<F>|null $nodeType
     * @return (F is null?Node[]:F[])
     */
    public function getNodes(?string $nodeType = null): array
    {
        if ($nodeType === null) {
            return $this->nodes;
        }

        return array_filter($this->nodes, static fn($node): bool => $node instanceof $nodeType);
    }

    public function getChildren(): array
    {
        return $this->nodes;
    }

    public function removeNode(int $key): self
    {
        $result = clone $this;
        unset($result->nodes[$key]);

        return $result;
    }

    public function replaceNode(int $key, Node $node): self
    {
        $result = clone $this;
        $result->nodes[$key] = $node;

        return $result;
    }

    public function getTitle(): ?TitleNode
    {
        foreach ($this->nodes as $node) {
            if ($node instanceof SectionNode && $node->getTitle()->getLevel() === 1) {
                return $node->getTitle();
            }
        }

        return null;
    }

    /**
     * @return TocNode[]
     */
    public function getTocs(): array
    {
        return $this->getRecursiveTocs($this);
    }

    /** @return TocNode[] */
    private function getRecursiveTocs(Node $parent): array
    {
        $tocs = [];
        foreach ($parent->getNodes() as $node) {
            if ($node instanceof TocNode) {
                $tocs[] = $node;
            }

            if ($node instanceof SectionNode) {
                $tocs = array_merge($tocs, $this->getRecursiveTocs($node));
            }
        }

        return $tocs;
    }

    /**
     * @return TitleNode[]
     */
    public function getTitles(): array
    {
        $titles = [];
        foreach ($this->nodes as $node) {
            if ($node instanceof SectionNode === false) {
                continue;
            }

            $titles = array_merge($titles, $node->getTitles());
        }

        return $titles;
    }

    /**
     * @param string|Node $node
     */
    public function addNode($node): void
    {
        if (is_string($node)) {
            $node = new RawNode($node);
        }

        $this->nodes[] = $node;
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

    public function addDependency(string $dependencyName): void
    {
        if (in_array($dependencyName, $this->dependencies, true)) {
            return;
        }

        $this->dependencies[] = $dependencyName;
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * @param mixed $default
     *
     * @return string|Node
     */
    public function getVariable(string $name, $default)
    {
        return $this->variables[$name] ?? $default;
    }

    /** @param string|Node $value */
    public function addVariable(string $name, $value): void
    {
        $this->variables[$name] = $value;
    }

    /** @param array<string, string> $links */
    public function setLinks(array $links): void
    {
        $this->links = $links;
    }

    public function getLink(string $name): ?string
    {
        return $this->links[strtolower(trim($name))] ?? null;
    }
}
