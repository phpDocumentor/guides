<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\MainNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

/**
 * Marks the document as LaTeX main
 */
class LaTeXMain extends BaseDirective
{
    public function getName(): string
    {
        return 'latex-main';
    }

    /** {@inheritDoc} */
    public function processNode(
        DocumentParserContext $documentParserContext,
        Directive $directive,
    ): Node {
        return new MainNode($directive->getData());
    }
}
