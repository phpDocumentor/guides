<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

interface TextRole
{
    public function getName(): string;

    /** @return string[] */
    public function getAliases(): array;

    /**
     * @param string $content the content with backslash escapes removed per spec
     * @param string $rawContent the raw content, including backslash escapes
     */
    public function processNode(
        DocumentParserContext $documentParserContext,
        string $role,
        string $content,
        string $rawContent,
    ): InlineNode;
}
