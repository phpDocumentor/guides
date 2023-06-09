<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

final class NewlineInlineNode extends InlineNode
{
    public const TYPE = 'newline';

    public function __construct()
    {
        parent::__construct(self::TYPE);
    }
}
