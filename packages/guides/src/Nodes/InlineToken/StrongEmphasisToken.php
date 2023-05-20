<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

final class StrongEmphasisToken extends InlineMarkupToken
{
    public const TYPE = 'strong';

    public function __construct(string $id, string $value)
    {
        parent::__construct(self::TYPE, $id, $value);
    }
}
