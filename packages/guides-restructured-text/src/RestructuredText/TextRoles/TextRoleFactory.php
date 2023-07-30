<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

interface TextRoleFactory
{
    public function getTextRole(string $name, string|null $domain = null): TextRole;

    public function registerTextRole(TextRole $textRole): void;

    public function replaceTextRole(TextRole $newTextRole): void;

    public function setDefaultTextRole(string $roleName): void;

    public function getDefaultTextRole(): TextRole;
}
