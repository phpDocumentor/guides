<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Links;

final class Link
{
    public const TYPE_LINK = 'link';
    public const TYPE_ANCHOR = 'anchor';

    public function __construct(private readonly string $name, private readonly string $url, private readonly string $type)
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
