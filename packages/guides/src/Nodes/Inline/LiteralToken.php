<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

final class LiteralToken extends InlineMarkupToken
{
    public const TYPE = 'literal';

    public function __construct(string $value)
    {
        parent::__construct(self::TYPE, $value);
    }
}
