<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

abstract class AbstractLinkToken extends InlineMarkupToken
{
    abstract public function getUrl(): string;

    abstract public function getText(): string;
}
