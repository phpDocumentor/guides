<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

interface LinkTargetNode
{
    public function getLinkType(): string;

    public function getId(): string;

    public function getLinkText(): string;
}
