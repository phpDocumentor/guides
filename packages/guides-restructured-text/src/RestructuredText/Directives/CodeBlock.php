<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\OptionMapper\CodeNodeOptionMapper;
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
    public function __construct(private readonly CodeNodeOptionMapper $codeNodeOptionMapper)
    {
    }

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
        $this->codeNodeOptionMapper->apply($node, $directive->getOptions());

        return $node;
    }
}
