<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

final class EmphasisToken extends ValueToken
{
    public const TYPE = 'emphasis';

    public function __construct(string $id, string $value)
    {
        parent::__construct(self::TYPE, $id, $value);
    }
}
