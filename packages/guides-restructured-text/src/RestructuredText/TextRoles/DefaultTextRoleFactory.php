<?php

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

class DefaultTextRoleFactory implements TextRoleFactory
{
    /** @var TextRole[] $textRoles */
    private array $textRoles;

    public function __construct()
    {
        $this->textRoles = [
            new EmphasisTextRole(),
        ];
    }


    /**
     * @throws TextRoleNotFoundException
     */
    public function getTextRole(string $name): TextRole
    {
        // First look for a textrole with the exact name
        foreach ($this->textRoles as $textRole) {
            if ($textRole->getName() === $name) {
                return $textRole;
            }
        }

        foreach ($this->textRoles as $textRole) {
            if (in_array($name, $textRole->getAliases())) {
                return $textRole;
            }
        }
        throw new TextRoleNotFoundException('No text role for "' . $name . '" found.');
    }
}
