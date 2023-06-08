<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

final class NewlineNode extends InlineMarkupToken
{
    public const TYPE = 'newline';

    public function __construct()
    {
        parent::__construct(self::TYPE, '');
    }
}
