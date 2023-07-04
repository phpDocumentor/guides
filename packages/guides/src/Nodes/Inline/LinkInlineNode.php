<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

interface LinkInlineNode
{
    public function getTargetReference(): string;

    public function setUrl(string $url): void;

    public function getUrl(): string;
}
