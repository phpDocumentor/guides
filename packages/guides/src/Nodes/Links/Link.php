<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Links;

final class Link
{
    public const TYPE_LINK = 'link';
    public const TYPE_ANCHOR = 'anchor';

    public function __construct(private string $name, private string $url, private string $type)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
