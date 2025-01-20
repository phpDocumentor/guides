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

use function in_array;
use function strtolower;

final class DefaultTextRoleFactory implements TextRoleFactory
{
    /** @var TextRole[] */
    private array $textRoles;

    /**
     * @param iterable<TextRole> $textRoles
     * @param array<string, TextRole[]> $domains
     */
    public function __construct(
        private readonly TextRole $genericTextRole,
        private TextRole $defaultTextRole,
        iterable $textRoles = [],
        private readonly array $domains = [],
    ) {
        $this->textRoles = [...$textRoles];
    }

    public function registerTextRole(TextRole $textRole): void
    {
        $this->textRoles[] = $textRole;
    }

    public function replaceTextRole(TextRole $newTextRole): void
    {
        foreach ($this->textRoles as &$textRole) {
            if ($textRole->getName() !== $newTextRole->getName()) {
                continue;
            }

            $textRole = $newTextRole;
        }

        $this->textRoles[] = $newTextRole;
    }

    public function getTextRole(string $name, string|null $domain = null): TextRole
    {
        $normalizedName = strtolower($name);
        if ($normalizedName === 'default') {
            return $this->defaultTextRole;
        }

        if ($domain === null) {
            return $this->findTextRole($this->textRoles, $normalizedName);
        }

        if (isset($this->domains[$domain])) {
            return $this->findTextRole($this->domains[$domain], $normalizedName);
        }

        return $this->genericTextRole;
    }

    /** @param TextRole[] $textRoles */
    private function findTextRole(array $textRoles, string $name): TextRole
    {
        // First look for a textrole with the exact name
        foreach ($textRoles as $textRole) {
            if ($textRole->getName() === $name) {
                return $textRole;
            }
        }

        // Textrole name takes precedence over alias
        foreach ($textRoles as $textRole) {
            if (in_array($name, $textRole->getAliases(), true)) {
                return $textRole;
            }
        }

        return $this->genericTextRole;
    }

    public function setDefaultTextRole(string $roleName): void
    {
        $newDefault = $this->getTextRole($roleName);
        if ($newDefault instanceof GenericTextRole) {
            $newDefault->setBaseRole($roleName);
        }

        $this->defaultTextRole = $this->getTextRole($roleName);
    }

    public function getDefaultTextRole(): TextRole
    {
        return $this->defaultTextRole;
    }
}
