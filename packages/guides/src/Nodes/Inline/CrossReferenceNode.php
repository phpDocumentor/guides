<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

/**
 * A cross-reference node is a link node that can reference targets outside of
 * the current document.
 *
 * Examples are external URLs, other documents or sections in the project and
 * other Intersphinx projects.
 */
interface CrossReferenceNode extends LinkInlineNode
{
}
