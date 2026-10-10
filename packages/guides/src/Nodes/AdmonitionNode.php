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

/** @extends CompoundNode<Node> */
class AdmonitionNode extends CompoundNode implements HasAttachedNodes
{
    /** @param Node[] $value */
    public function __construct(private readonly string $name, private readonly InlineCompoundNode|null $title, private readonly string $text, array $value, private readonly bool $isTitled = false)
    {
        parent::__construct($value);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): InlineCompoundNode|null
    {
        return $this->title;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function isTitled(): bool
    {
        return $this->isTitled;
    }

    /** @return array<string, InlineCompoundNode> */
    public function getAttachedNodes(): array
    {
        return $this->title === null ? [] : ['title' => $this->title];
    }
}
