<?php

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

interface TextRoleFactory
{
    /**
     * @throws TextRoleNotFoundException
     */
    public function getTextRole(string $name): TextRole;
}
