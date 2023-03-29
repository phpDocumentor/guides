<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

final class GenericTextRoleToken extends ValueToken
{
    public const TYPE = 'role';

    private string $role;

    public function __construct(string $id, string $role, string $value)
    {
        $this->role = $role;
        parent::__construct(self::TYPE, $id, $value);
    }

    public function getRole(): string
    {
        return $this->role;
    }
}
