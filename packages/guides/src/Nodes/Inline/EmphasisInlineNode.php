<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

final class EmphasisInlineNode extends InlineNode
{
    public const TYPE = 'emphasis';

    public function __construct(string $value)
    {
        parent::__construct(self::TYPE, $value);
    }
}
