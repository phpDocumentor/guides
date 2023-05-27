<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

interface TextRoleFactory
{
    public function getTextRole(string $name, string|null $domain = null): TextRole;
}
