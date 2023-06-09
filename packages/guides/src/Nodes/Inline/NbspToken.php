<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

final class NbspToken extends InlineMarkupToken
{
    public const TYPE = 'nbsp';

    public function __construct()
    {
        parent::__construct(self::TYPE);
    }
}
