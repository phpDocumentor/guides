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
        private TextRole $defaultTextRole,
        iterable $textRoles = [],
        private array $domains = [],
    ) {
        $this->textRoles = [...$textRoles];
    }

    public function registerTextRole(TextRole $textRole): void
    {
        $this->textRoles[] = $textRole;
    }

    /** @param TextRole[] $textRoles */
    public function registerDomain(string $domainName, array $textRoles): void
    {
        $this->domains[$domainName] = $textRoles;
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
        if ($name === 'default') {
            return $this->defaultTextRole;
        }

        if ($domain === null) {
            return $this->findTextRole($this->textRoles, $name);
        }

        if (isset($this->domains[$domain])) {
            return $this->findTextRole($this->domains[$domain], $name);
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
