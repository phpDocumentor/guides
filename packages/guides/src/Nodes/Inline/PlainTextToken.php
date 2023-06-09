<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

final class PlainTextToken extends InlineMarkupToken
{
    public const TYPE = 'plain';

    public function __construct(string $value)
    {
        parent::__construct(self::TYPE, $value);
    }

    public function append(PlainTextToken $token): void
    {
        $this->value .= $token->getValue();
    }
}
