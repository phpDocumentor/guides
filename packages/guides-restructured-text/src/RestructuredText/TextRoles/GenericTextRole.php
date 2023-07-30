<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\GenericTextRoleInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

class GenericTextRole extends BaseTextRole
{
    protected string $name = 'default';
    protected string|null $baseRole = null;

    public function processNode(
        DocumentParserContext $documentParserContext,
        string $role,
        string $content,
        string $rawContent,
    ): GenericTextRoleInlineNode {
        return new GenericTextRoleInlineNode($this->baseRole ?? $role, $content, $this->getClass());
    }

    public function getBaseRole(): string|null
    {
        return $this->baseRole;
    }

    public function setBaseRole(string|null $baseRole): void
    {
        $this->baseRole = $baseRole;
    }
}
