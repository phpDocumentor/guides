<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

use function trim;

/**
 * https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#directive-highlight
 */
class HighlightDirective extends ActionDirective
{
    public function getName(): string
    {
        return 'highlight';
    }

    public function processAction(
        BlockContext $blockContext,
        Directive $directive,
    ): void {
        $blockContext->getDocumentParserContext()->setCodeBlockDefaultLanguage(trim($directive->getData()));
    }
}
