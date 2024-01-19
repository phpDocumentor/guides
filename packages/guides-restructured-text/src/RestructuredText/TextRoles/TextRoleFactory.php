<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

interface TextRoleFactory
{
    public function getTextRole(string $name, string|null $domain = null): TextRole;

    public function registerTextRole(TextRole $textRole): void;

    public function replaceTextRole(TextRole $newTextRole): void;

    public function setDefaultTextRole(string $roleName): void;

    public function getDefaultTextRole(): TextRole;
}
