<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

final class PlainTextToken extends InlineMarkupToken
{
    public const TYPE = 'plain';

    public function __construct(string $id, string $value)
    {
        parent::__construct(self::TYPE, $id, $value);
    }

    public function append(PlainTextToken $token): void
    {
        $this->value .= $token->getValue();
    }
}
