<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

/**
 * Extend this class to create a directive that does some actions, for example on the parser context, without
 * creating a node.
 */
abstract class ActionDirective extends BaseDirective
{
    public function process(
        DocumentParserContext $documentParserContext,
        Directive $directive,
    ): Node|null {
        $this->processAction($documentParserContext, $directive);

        return null;
    }

    /**
     * @param DocumentParserContext $documentParserContext the current document context with the content
     *    of the directive
     * @param Directive $directive parsed directive containing options and variable
     */
    abstract public function processAction(
        DocumentParserContext $documentParserContext,
        Directive $directive,
    ): void;
}
