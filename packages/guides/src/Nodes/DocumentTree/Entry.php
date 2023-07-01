<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\DocumentTree;

interface Entry
{
    public function addChild(Entry $child): void;

    /** @return Entry[] */
    public function getChildren(): array;
}
