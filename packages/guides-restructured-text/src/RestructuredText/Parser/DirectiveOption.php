<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

class DirectiveOption
{
    private string $name;

    /** @var scalar|null */
    private $value;

    /**
     * @param scalar|null $value
     */
    public function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return scalar|null
     */
    public function getValue()
    {
        return $this->value;
    }

    public function appendValue(string $append): void
    {
        $this->value = ((string) $this->value) . $append;
    }
}
