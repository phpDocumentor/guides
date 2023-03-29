<?php

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Span\EmphasisToken;
use phpDocumentor\Guides\Span\InlineMarkupToken;

class EmphasisTextRole implements TextRole
{

    const NAME = 'emphasis';

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return ['italic'];
    }

    public function processNode(string $content): InlineMarkupToken
    {
        return new EmphasisToken('??', $content);
    }
}
