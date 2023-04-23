<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Lists;

/** @deprecated Needs to be removed duplicate of {@see ListItemNode} */
final class ListItem
{
    public function __construct(private readonly string $prefix, private readonly bool $ordered, private readonly int $depth, private mixed $text)
    {
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

    public function getText(): mixed
    {
        return $this->text;
    }

    public function setText(mixed $text): void
    {
        $this->text = $text;
    }

    /** @return mixed[] */
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
