<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

/**
 * Represents a link to an external source or email
 */
class HyperLinkNode extends AbstractLinkInlineNode
{
    public function __construct(string $value, string $targetReference)
    {
        parent::__construct('link', $targetReference, $value);
    }
}
