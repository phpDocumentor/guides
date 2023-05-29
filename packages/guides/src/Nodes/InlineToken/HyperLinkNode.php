<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

/**
 * Represents a link to an external source or email
 */
class HyperLinkNode extends InlineMarkupToken
{
    /** @param string[] $token */
    public function __construct(string $id, string $value = '', array $token = [])
    {
        parent::__construct('link', $id, $value, $token);
    }
}
