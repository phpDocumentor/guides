<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

use phpDocumentor\Guides\Nodes\Node;

interface LinkInlineNode extends Node
{
    public function getTargetReference(): string;

    public function setUrl(string $url): void;

    public function getUrl(): string;
}
