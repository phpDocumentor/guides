<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\InlineToken\EmphasisToken;
use phpDocumentor\Guides\Nodes\InlineToken\GenericTextRoleToken;
use phpDocumentor\Guides\Nodes\InlineToken\InlineMarkupToken;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;

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

    public function processNode(
        ParserContext $parserContext,
        string $id,
        string $role,
        string $content
    ): InlineMarkupToken
    {
        return new GenericTextRoleToken('??', $role, $content);
    }
}
