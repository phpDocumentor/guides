<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use function strval;

class DirectiveOption
{
    public function __construct(private readonly string $name, private string|int|float|bool|null $value = null)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string|int|float|bool|null
    {
        return $this->value;
    }

    public function toString(): string
    {
        return strval($this->value);
    }

    public function appendValue(string $append): void
    {
        $this->value = ((string) $this->value) . $append;
    }
}
