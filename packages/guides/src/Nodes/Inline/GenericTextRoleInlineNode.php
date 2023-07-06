<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

class GenericTextRoleInlineNode extends InlineNode
{
    public function __construct(private readonly string $role, private readonly string $content, private readonly string $class = '')
    {
        parent::__construct($role, $content);
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getClass(): string
    {
        return $this->class;
    }
}
