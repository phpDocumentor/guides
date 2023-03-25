<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\TextRoles;

use OutOfBoundsException;

/**
 * @implements \Iterator<int,string>
 */
class TokenIterator implements \Iterator
{
    /** @var int[] */
    private array $snapShot = [];
    private int $position = 0;
    /** @var string[]  */
    private array $tokens;

    /** @param string[] $tokens */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function current()
    {
        if ($this->valid() === false) {
            throw new OutOfBoundsException('Attempted to token that does not exist');
        }

        return $this->tokens[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->tokens[$this->position]);
    }

    public function getNext(): ?string
    {
        return $this->tokens[$this->position + 1] ?? null;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function snapShot(): void
    {
        $this->snapShot[] = $this->position;
    }

    public function restore(): void
    {
        $this->position = array_pop($this->snapShot) ?? 0;
    }
}
