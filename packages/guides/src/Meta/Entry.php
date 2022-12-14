<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Meta;

interface Entry
{
    public function addChild(ChildEntry $child): void;

    /** @return Entry[] */
    public function getChildren(): array;
}
