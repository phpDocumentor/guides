<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;

use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use function trim;

/**
 * Renders a code block, example:
 *
 * .. code-block:: php
 *
 *      <?php
 *
 *      echo "Hello world!\n";
 *
 * @link https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#directive-code-block
 */
class CodeBlock extends Directive
{
    public function getName(): string
    {
        return 'code-block';
    }

    public function process(
        DocumentParserContext $documentParserContext,
        string $variable,
        string                $data,
        array                 $options
    ): ?Node {

        $node = new CodeNode(
            $documentParserContext->getDocumentIterator()->toArray()
        );

        $node->setLanguage(trim($data));
        $this->setStartingLineNumberBasedOnOptions($options, $node);

        $document = $documentParserContext->getDocument();
        if ($variable !== '') {
            $document->addVariable($variable, $node);
            return null;
        }

        return $node;
    }

    /**
     * @param mixed[] $options
     */
    private function setStartingLineNumberBasedOnOptions(array $options, CodeNode $node): void
    {
        $startingLineNumber = null;
        if (isset($options['linenos'])) {
            $startingLineNumber = 1;
        }

        $startingLineNumber = $options['number-lines'] ?? $options['lineno-start'] ?? $startingLineNumber;

        if ($startingLineNumber === null) {
            return;
        }

        $node->setStartingLineNumber((int) $startingLineNumber);
    }
}
