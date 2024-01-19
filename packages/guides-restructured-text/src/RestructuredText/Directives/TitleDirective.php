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

/**
 * Add a meta title to the document
 *
 * .. title:: Page title
 */
final class TitleDirective extends ActionDirective
{
    public function getName(): string
    {
        return 'title';
    }

    public function processAction(BlockContext $blockContext, Directive $directive): void
    {
        $document = $blockContext->getDocumentParserContext()->getDocument();
        $document->setMetaTitle($directive->getData());
    }
}
