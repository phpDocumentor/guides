<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

abstract class AbstractLinkInlineNode extends InlineNode
{
    abstract public function getUrl(): string;

    abstract public function getText(): string;
}
