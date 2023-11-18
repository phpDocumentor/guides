<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\OptionMapper\CodeNodeOptionMapper;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;

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
class CodeBlockDirective extends BaseDirective
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
        BlockContext $blockContext,
        Directive $directive,
    ): Node|null {
        $node = new CodeNode(
            $blockContext->getDocumentIterator()->toArray(),
        );

        if (trim($directive->getData()) !== '') {
            $node->setLanguage(trim($directive->getData()));
        } else {
            $node->setLanguage($blockContext->getDocumentParserContext()->getCodeBlockDefaultLanguage());
        }

        $this->setStartingLineNumberBasedOnOptions($directive->getOptions(), $node);
        $this->setCaptionBasedOnOptions($directive->getOptions(), $node);
        $this->codeNodeOptionMapper->apply($node, $directive->getOptions());

        if ($directive->getVariable() !== '') {
            $document = $blockContext->getDocumentParserContext()->getDocument();
            $document->addVariable($directive->getVariable(), $node);

            return null;
        }

        return $node;
    }

    /** @param array<string, DirectiveOption> $options */
    private function setStartingLineNumberBasedOnOptions(array $options, CodeNode $node): void
    {
        $startingLineNumber = null;
        if (isset($options['linenos']) || isset($options['number-lines'])) {
            $startingLineNumber = 1;
        }

        if (isset($options['number-lines'])) {
            $startingLineNumber = $options['number-lines']->getValue() ?? $startingLineNumber;
        } elseif (isset($options['lineno-start'])) {
            $startingLineNumber = $options['lineno-start']->getValue() ?? $startingLineNumber;
        }

        if ($startingLineNumber === null) {
            return;
        }

        $node->setStartingLineNumber((int) $startingLineNumber);
    }

    /** @param DirectiveOption[] $options */
    private function setCaptionBasedOnOptions(array $options, CodeNode $node): void
    {
        $caption = null;
        if (isset($options['caption'])) {
            $caption = (string) $options['caption']->getValue();
        }

        $node->setCaption($caption);
    }
}
