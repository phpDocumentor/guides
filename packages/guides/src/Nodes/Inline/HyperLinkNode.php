<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

/**
 * Represents a link to an external source or email
 */
class HyperLinkNode extends InlineMarkupToken
{
    private string $url;

    public function __construct(string $value, string|null $url = null)
    {
        $this->url = $url ?? $value;

        parent::__construct('link', $value);
    }

    public function getLink(): string
    {
        return $this->value;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}
