<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

class GenericTextRoleToken extends InlineMarkupToken
{
    public const TYPE = 'role';

    public function __construct(string $id, private readonly string $role, private readonly string $content)
    {
        parent::__construct($role, $id, $content);
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
