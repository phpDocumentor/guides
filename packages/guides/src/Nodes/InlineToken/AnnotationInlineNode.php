<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

/**
 * This node an annotation, for example citation or footnote
 */
abstract class AnnotationInlineNode extends ValueToken
{
    abstract public function getName(): string;
}
