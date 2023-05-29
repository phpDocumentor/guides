<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

/**
 * Represents a link to an external source or email
 */
class HyperLinkNode extends InlineMarkupToken
{
    public function __construct(string $id, string $value, private readonly string $url)
    {
        parent::__construct('link', $id, $value);
    }

    public function getLink(): string
    {
        return $this->value;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
