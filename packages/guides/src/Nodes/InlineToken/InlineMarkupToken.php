<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

class InlineMarkupToken
{
    public function __construct(private readonly string $type, private readonly string $id, private readonly string $content)
    {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
