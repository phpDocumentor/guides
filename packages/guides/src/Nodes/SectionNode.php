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

use function array_merge;
use function in_array;

/** @extends CompoundNode<Node> */
final class SectionNode extends CompoundNode implements LinkTargetNode
{
    public const STD_LABEL = 'std:label';
    public const STD_TITLE = 'std:title';

    /** @var string[] */
    private array $indexTerms = [];

    public function __construct(private readonly TitleNode $title)
    {
        parent::__construct([$title]);
    }

    /**
     * Records that a `.. index::` entry resolved to this section, e.g. so a
     * theme's section template can render it as a search-key data attribute.
     * Idempotent -- adding the same term twice has no extra effect.
     */
    public function addIndexTerm(string $term): void
    {
        if (in_array($term, $this->indexTerms, true)) {
            return;
        }

        $this->indexTerms[] = $term;
    }

    /** @return string[] */
    public function getIndexTerms(): array
    {
        return $this->indexTerms;
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

    public function getLinkType(): string
    {
        return self::STD_LABEL;
    }

    public function getId(): string
    {
        return $this->getTitle()->getId();
    }

    public function getLinkText(): string
    {
        return $this->getTitle()->toString();
    }
}
