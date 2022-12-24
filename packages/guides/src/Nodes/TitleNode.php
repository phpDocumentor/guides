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

use Symfony\Component\String\Slugger\AsciiSlugger;
use Webmozart\Assert\Assert;

class TitleNode extends Node
{
    /** @var int */
    protected $level;

    /** @var string */
    protected $id;

    /** @var string */
    protected $target = '';

    public function __construct(Node $value, int $level)
    {
        parent::__construct($value);
        Assert::isInstanceOf($value, SpanNode::class);

        $this->level = $level;
        $this->id = (new AsciiSlugger())->slug($value->getValueString())->lower()->toString();
    }

    public static function emptyNode(): self
    {
        return new TitleNode(new SpanNode('<Unknown>'), 0);
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
}
