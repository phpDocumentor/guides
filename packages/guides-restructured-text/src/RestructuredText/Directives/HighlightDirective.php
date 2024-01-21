<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

use function trim;

/**
 * https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#directive-highlight
 */
final class HighlightDirective extends ActionDirective
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
