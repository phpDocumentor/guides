<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

interface MultipleLinkTargetsNode
{
    /** @return string[] */
    public function getAdditionalIds(): array;
}
