<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\RubricNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

/**
 * The "rubric" directive inserts a "rubric" node into the document tree. A rubric is like an informal heading
 * that does not correspond to the document's structure.
 *
 * @see https://docutils.sourceforge.io/docs/ref/rst/directives.html#rubric
 */
class RubricDirective extends BaseDirective
{
    public function getName(): string
    {
        return 'rubric';
    }

    public function processNode(
        DocumentParserContext $documentParserContext,
        Directive $directive,
    ): Node {
        return new RubricNode($directive->getData());
    }
}
