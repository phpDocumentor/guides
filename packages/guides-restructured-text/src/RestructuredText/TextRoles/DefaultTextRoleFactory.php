<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use Psr\Log\LoggerInterface;
use function in_array;

class DefaultTextRoleFactory implements TextRoleFactory
{
    /**
     * @param iterable<TextRole> $textRoles
     * @param array<string, TextRole[]> $domains
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TextRole $genericTextRole,
        private iterable $textRoles = [],
        private array $domains = []
    )
    {
    }

    public function registerTextRole(TextRole $textRoles) {
        $this->textRoles[] = $textRoles;
    }

    public function getTextRole(string $name, string|null $domain = null): TextRole
    {
        if ($domain === null) {
            return $this->findTextRole($this->textRoles, $name, $domain);
        }
        if (isset($this->domains[$domain])) {
            return  $this->findTextRole($this->domains[$domain], $name, $domain);
        }
        $this->logger->warning(sprintf('No text role for "%s:%s" found.', $domain, $name));
        return $this->genericTextRole;
    }

    /**
     * @param iterable<TextRole> $textRoles
     */
    public function findTextRole(iterable $textRoles, string $name, string|null $domain): TextRole
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

        if ($domain === null) {
            $this->logger->warning(sprintf('No text role for "%s" found.', $name));
        } else {
            $this->logger->warning(sprintf('No text role for "%s" found for domain "%s".', $name, $domain));
        }
        return $this->genericTextRole;
    }
}
