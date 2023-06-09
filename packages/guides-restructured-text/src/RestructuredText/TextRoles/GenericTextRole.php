<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\GenericTextRoleToken;
use phpDocumentor\Guides\Nodes\Inline\InlineMarkupToken;
use phpDocumentor\Guides\ParserContext;

class GenericTextRole implements TextRole
{
    final public const NAME = 'default';

    public function getName(): string
    {
        return self::NAME;
    }

    /** @inheritDoc */
    public function getAliases(): array
    {
        return [];
    }

    /** @return GenericTextRoleToken */
    public function processNode(
        ParserContext $parserContext,
        string $role,
        string $content,
    ): InlineMarkupToken {
        return new GenericTextRoleToken($role, $content);
    }
}
