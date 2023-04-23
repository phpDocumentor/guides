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

/** @extends CompoundNode<TextNode> */
class TitleNode extends CompoundNode
{
    protected string $target = '';
    private string $title;

    public function __construct(SpanNode $value, protected int $level, protected string $id)
    {
        $this->title = (string) $value->getValue();

        parent::__construct([$value]);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public static function emptyNode(): self
    {
        return new TitleNode(new SpanNode('<Unknown>'), 0, '');
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
