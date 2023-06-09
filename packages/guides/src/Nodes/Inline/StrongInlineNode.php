<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

final class StrongInlineNode extends InlineNode
{
    public const TYPE = 'strong';

    public function __construct(string $value)
    {
        parent::__construct(self::TYPE, $value);
    }
}
