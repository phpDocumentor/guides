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

use phpDocumentor\Guides\Nodes\Inline\PlainTextToken;

/** @extends CompoundNode<InlineNode> */
class TitleNode extends CompoundNode
{
    protected string $target = '';

    public function __construct(InlineNode $value, protected int $level, protected string $id)
    {
        parent::__construct([$value]);
    }

    public static function emptyNode(): self
    {
        return new TitleNode(new InlineNode([new PlainTextToken('<Unknown>')]), 0, '');
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function toString(): string
    {
        $result = '';
        foreach ($this->value as $child) {
            $result .= $child->toString();
        }

        return $result;
    }
}
