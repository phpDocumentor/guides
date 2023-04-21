<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\InlineToken\EmphasisToken;
use phpDocumentor\Guides\Nodes\InlineToken\InlineMarkupToken;

class EmphasisTextRole implements TextRole
{
    public const NAME = 'emphasis';

    public function getName(): string
    {
        return self::NAME;
    }

    /** @inheritDoc */
    public function getAliases(): array
    {
        return ['italic'];
    }

    public function processNode(string $content): InlineMarkupToken
    {
        return new EmphasisToken('??', $content);
    }
}
