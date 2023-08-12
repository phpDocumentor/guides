<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\GenericTextRoleInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

class LiteralTextRole extends BaseTextRole
{
    protected string $name = 'literal';

    /** @return string[] */
    public function getAliases(): array
    {
        return ['code'];
    }

    public function processNode(
        DocumentParserContext $documentParserContext,
        string $role,
        string $content,
        string $rawContent,
    ): GenericTextRoleInlineNode {
        return new GenericTextRoleInlineNode('literal', $rawContent, $this->getClass());
    }
}
