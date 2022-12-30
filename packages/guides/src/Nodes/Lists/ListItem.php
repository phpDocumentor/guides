<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Lists;

/** @deprecated Needs to be removed duplicate of {@see ListItemNode} */
final class ListItem
{
    private string $prefix;

    private bool $ordered;

    private int $depth;

    /** @var mixed */
    private $text;

    /**
     * @param mixed $text
     */
    public function __construct(string $prefix, bool $ordered, int $depth, $text)
    {
        $this->prefix = $prefix;
        $this->ordered = $ordered;
        $this->depth = $depth;
        $this->text = $text;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function isOrdered(): bool
    {
        return $this->ordered;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text): void
    {
        $this->text = $text;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [
            'prefix' => $this->prefix,
            'ordered' => $this->ordered,
            'depth' => $this->depth,
            'text' => $this->text,
        ];
    }
}
