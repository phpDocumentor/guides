<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

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
abstract class SubDirective extends Directive
{
    /** {@inheritDoc} */
    final public function process(
        DocumentParserContext $documentParserContext,
        \phpDocumentor\Guides\RestructuredText\Parser\Directive $directive,
    ): Node|null {
        $subParser = $documentParserContext->getParser()->getSubParser();
        $document = $subParser->parse(
            $documentParserContext->getContext(),
            implode("\n", $documentParserContext->getDocumentIterator()->toArray()),
        );

        $node = $this->processSub($document, $directive);

        if ($node === null) {
            return null;
        }

        return $node->withOptions($this->optionsToArray($directive->getOptions()));
    }

    protected function processSub(
        DocumentNode $document,
        \phpDocumentor\Guides\RestructuredText\Parser\Directive $directive,
    ): Node|null {
        return null;
    }
}
