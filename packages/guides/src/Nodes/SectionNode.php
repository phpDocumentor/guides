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

/** @extends CompoundNode<Node> */
final class SectionNode extends CompoundNode implements LinkTargetNode
{
    public const STD_LABEL = 'std:label';
    public const STD_TITLE = 'std:title';

    public function __construct(private readonly TitleNode $title)
    {
        parent::__construct([$title]);
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
