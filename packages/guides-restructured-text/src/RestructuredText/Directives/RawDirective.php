<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\RawNode;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

/**
 * Renders a raw block, example:
 *
 * .. raw::
 *
 *      <u>Underlined!</u>
 *
 * @link https://docutils.sourceforge.io/docs/ref/rst/directives.html#raw-data-pass-through
 */
class RawDirective extends Directive
{
    public function getName(): string
    {
        return 'raw';
    }

    /**
     * @param DocumentParserContext $documentParserContext
     * @param string[] $options
     */
    public function process(
        DocumentParserContext $documentParserContext,
        string $variable,
        string                $data,
        array                 $options
    ): ?Node {

        $node = new RawNode(implode("\n", $documentParserContext->getDocumentIterator()->toArray()));

        $document = $documentParserContext->getDocument();
        if ($variable !== '') {
            $document->addVariable($variable, $node);
            return null;
        }

        return $node;
    }
}
