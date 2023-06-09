<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

class GenericTextRoleInlineNode extends InlineNode
{
    public const TYPE = 'role';

    public function __construct(private readonly string $role, private readonly string $content)
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
}
