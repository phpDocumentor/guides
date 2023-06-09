<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\InlineToken\InlineMarkupToken;
use phpDocumentor\Guides\ParserContext;

interface TextRole
{
    public function getName(): string;

    /** @return string[] */
    public function getAliases(): array;

    public function processNode(
        ParserContext $parserContext,
        string $role,
        string $content,
    ): InlineMarkupToken;
}
