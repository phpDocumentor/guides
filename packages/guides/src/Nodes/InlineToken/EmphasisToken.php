<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

final class EmphasisToken extends InlineMarkupToken
{
    public const TYPE = 'emphasis';

    public function __construct(string $value)
    {
        parent::__construct(self::TYPE, $value);
    }
}
