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

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\QuoteNode;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;

/**
 * An epigraph is an apposite (suitable, apt, or pertinent) short inscription,
 * often a quotation or poem, at the beginning of a document or section.
 * The "epigraph" directive produces an "epigraph"-class block quote.
 *
 * https://docutils.sourceforge.io/docs/ref/rst/directives.html#epigraph
 */
#[Attributes\Directive(name: 'epigraph')]
final class EpigraphDirective extends SubDirective
{
    public function createNode(DirectiveNode $directiveNode): Node
    {
        return new QuoteNode($directiveNode->getChildren(), ['epigraph']);
    }
}
