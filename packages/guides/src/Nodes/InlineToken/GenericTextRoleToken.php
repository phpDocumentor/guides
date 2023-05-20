<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

final class GenericTextRoleToken extends InlineMarkupToken
{
    public function __construct(string $id, private readonly string $role, string $value)
    {
        parent::__construct($role, $id, $value);
    }

    public function getRole(): string
    {
        return $this->role;
    }
}
