<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
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
class CodeBlock extends BaseDirective
{
    public function getName(): string
    {
        return 'code-block';
    }

    /** {@inheritDoc} */
    public function getAliases(): array
    {
        return ['code'];
    }

    /** {@inheritDoc} */
    public function process(
        DocumentParserContext $documentParserContext,
        Directive $directive,
    ): Node|null {
        $node = new CodeNode(
            $documentParserContext->getDocumentIterator()->toArray(),
        );

        $node->setLanguage(trim($directive->getData()));
        $this->setStartingLineNumberBasedOnOptions($directive->getOptions(), $node);

        return $node;
    }

    /** @param mixed[] $options */
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
