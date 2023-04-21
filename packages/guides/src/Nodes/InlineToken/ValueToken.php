<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

class ValueToken extends InlineMarkupToken
{
    private string $value;

    public function __construct(string $type, string $id, string $value)
    {
        $this->value = $value;

        parent::__construct($type, $id, []);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
