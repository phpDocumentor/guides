<?php

namespace phpDocumentor\Guides\Nodes\InlineToken;

abstract class AbstractLinkToken extends InlineMarkupToken
{
    public abstract function getUrl(): string;

    public abstract function getText(): string;
}
