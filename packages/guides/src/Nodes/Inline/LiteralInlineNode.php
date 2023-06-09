<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

final class LiteralInlineNode extends InlineNode
{
    public const TYPE = 'literal';

    public function __construct(string $value)
    {
        parent::__construct(self::TYPE, $value);
    }
}
