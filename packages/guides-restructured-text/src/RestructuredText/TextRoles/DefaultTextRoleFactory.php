<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use function in_array;

class DefaultTextRoleFactory implements TextRoleFactory
{
    /** @var TextRole[] */
    private array $textRoles;

    /**
     * @param iterable<TextRole> $textRoles
     * @param array<string, TextRole[]> $domains
     */
    public function __construct(
        private readonly TextRole $genericTextRole,
        iterable $textRoles = [],
        private array $domains = [],
    ) {
        $this->textRoles = [...$textRoles];
    }

    public function registerTextRole(TextRole $textRoles): void
    {
        $this->textRoles[] = $textRoles;
    }

    public function getTextRole(string $name, string|null $domain = null): TextRole
    {
        if ($domain === null) {
            return $this->findTextRole($this->textRoles, $name, $domain);
        }

        if (isset($this->domains[$domain])) {
            return $this->findTextRole($this->domains[$domain], $name, $domain);
        }

        return $this->genericTextRole;
    }

    /** @param TextRole[] $textRoles */
    public function findTextRole(array $textRoles, string $name, string|null $domain): TextRole
    {
        // First look for a textrole with the exact name
        foreach ($textRoles as $textRole) {
            if ($textRole->getName() === $name) {
                return $textRole;
            }
        }

        // Textrole name takes precedence over alias
        foreach ($textRoles as $textRole) {
            if (in_array($name, $textRole->getAliases())) {
                return $textRole;
            }
        }

        return $this->genericTextRole;
    }
}
