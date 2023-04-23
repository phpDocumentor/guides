<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

use function array_merge;
use function array_unique;
use function trim;

/** @extends CompoundNode<Node> */
final class SectionNode extends CompoundNode implements TitledNode
{
    private string $identifier;
    /** @var string[] */
    private array $anchors = [];

    public function __construct(private readonly TitleNode $title)
    {
        $this->identifier = $title->getId();

        parent::__construct();
    }

    public function getTitlePlaintext(): string
    {
        return $this->getTitle()->getTitle();
    }

    public function getTitle(): TitleNode
    {
        return $this->title;
    }

    /** @return TitleNode[] */
    public function getTitles(): array
    {
        $titles = [$this->title];
        foreach ($this->value as $node) {
            if ($node instanceof self === false) {
                continue;
            }

            $titles = array_merge($titles, $node->getTitles());
        }

        return $titles;
    }

    public function addAnchor(string $anchor): void
    {
        $this->identifier = trim($anchor);
        $this->anchors[] = trim($anchor);
        $this->anchors = array_unique($this->anchors);
    }

    /** @return string[] */
    public function getAnchors(): array
    {
        return $this->anchors;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
