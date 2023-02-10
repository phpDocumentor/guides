<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

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
    /**
     * @param DocumentParserContext $documentParserContext
     * @param string[] $options
     */
    final public function process(
        DocumentParserContext $documentParserContext,
        string $variable,
        string                $data,
        array                 $options
    ): ?Node {
        $subParser = $documentParserContext->getParser()->getSubParser();
        $document = $subParser->parse(
            $documentParserContext->getContext(),
            implode("\n", $documentParserContext->getDocumentIterator()->toArray())
        );

        $newNode = $this->processSub($document, $variable, $data, $options);

        if ($newNode === null) {
            return null;
        }

        $document = $documentParserContext->getDocument();
        if ($variable !== '') {
            $document->addVariable($variable, $newNode);
            return null;
        }

        return $newNode;
    }

    /**
     * @param string[] $options
     */
    public function processSub(
        Node   $document,
        string $variable,
        string $data,
        array  $options
    ): ?Node {
        return null;
    }
}
