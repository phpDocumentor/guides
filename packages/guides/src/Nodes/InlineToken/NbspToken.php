<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

final class NbspToken extends InlineMarkupToken
{
    public const TYPE = 'nbsp';

    public function __construct(string $id)
    {
        parent::__construct(self::TYPE, $id, []);
    }
}
