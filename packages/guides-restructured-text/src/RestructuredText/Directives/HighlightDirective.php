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

use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Nodes\HighlightNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

use function trim;

/**
 * https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#directive-highlight
 */
#[\phpDocumentor\Guides\RestructuredText\Directives\Attributes\Directive(name: 'highlight')]
final class HighlightDirective extends ActionDirective
{
    /**
     * The HighlightNode produced here is picked up by the compiler's HighlightNodeTransformer, which applies the
     * language to every following node implementing ConsumesDefaultHighlightLanguage that doesn't already have an
     * explicit language set.
     */
    public function createNode(DirectiveNode $directiveNode, CompilerContextInterface $compilerContext): Node
    {
        return new HighlightNode(trim($directiveNode->getDirective()->getData()));
    }

    /** @deprecated kept for backwards compatibility, see {@see HighlightNode} for the current mechanism */
    public function processAction(
        BlockContext $blockContext,
        Directive $directive,
    ): void {
        $blockContext->getDocumentParserContext()->setCodeBlockDefaultLanguage(trim($directive->getData()));
    }
}
