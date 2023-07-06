<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

abstract class AbstractLinkInlineNode extends InlineNode implements LinkInlineNode
{
    private string $url = '';

    public function __construct(string $type, private readonly string $targetReference, string $value = '')
    {
        parent::__construct($type, $value);
    }

    public function getTargetReference(): string
    {
        return $this->targetReference;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
