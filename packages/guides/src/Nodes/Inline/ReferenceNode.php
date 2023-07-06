<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

/**
 * This class should be moved into Nodes, but right now the span parser is producing this.
 * I just want to get started to improve reference handling
 *
 * CrossReferences are references outside a document. As parsing is file based normal references are in document,
 * refering to other documents.
 *
 * Supported formats
 *
 *     :ref:`link`
 *     :ref:`custom text <link>`
 *
 * Cross references are resolved at the start of the rendering phase.
 */
class ReferenceNode extends AbstractLinkInlineNode implements CrossReferenceNode
{
    final public const TYPE = 'ref';

    public function __construct(
        string $targetReference,
        string $value = '',
    ) {
        parent::__construct(self::TYPE, $targetReference, $value);
    }
}
