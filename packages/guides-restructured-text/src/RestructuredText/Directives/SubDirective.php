<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

use function implode;

/**
 * A directive that parses the sub block and call the processSub that can
 * be overloaded, like :
 *
 * .. sub-directive::
 *      Some block of code
 *
 *      You can imagine anything here, like adding *emphasis*, lists or
 *      titles
 */
abstract class SubDirective extends BaseDirective
{
    /** {@inheritDoc} */
    final public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): Node|null {
        $subParser = $blockContext->getDocumentParserContext()->getParser()->getSubParser();
        $document = $subParser->parse(
            $blockContext->getDocumentParserContext()->getContext(),
            implode("\n", $blockContext->getDocumentIterator()->toArray()),
        );

        $node = $this->processSub($document, $directive);

        if ($node === null) {
            return null;
        }

        return $node->withOptions($this->optionsToArray($directive->getOptions()));
    }

    protected function processSub(
        DocumentNode $document,
        Directive $directive,
    ): Node|null {
        return null;
    }
}
