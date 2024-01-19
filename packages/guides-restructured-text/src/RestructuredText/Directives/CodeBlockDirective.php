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

use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\OptionMapper\CodeNodeOptionMapper;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;
use Psr\Log\LoggerInterface;

use function preg_match;
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
final class CodeBlockDirective extends BaseDirective
{
    /** @see https://regex101.com/r/I3KttH/1 */
    public const LINE_NUMBER_RANGES_REGEX = '/^\d+(-\d+)?(?:,\s*\d+(-\d+)?)*$/';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CodeNodeOptionMapper $codeNodeOptionMapper,
    ) {
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
        $this->setEmphasizeLinesBasedOnOptions($blockContext, $directive->getOptions(), $node);
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

    /** @param DirectiveOption[] $options */
    private function setEmphasizeLinesBasedOnOptions(BlockContext $blockContext, array $options, CodeNode $node): void
    {
        $emphasizeLines = null;
        if (isset($options['emphasize-lines'])) {
            $emphasizeLines = (string) $options['emphasize-lines']->getValue();
            if (!preg_match(self::LINE_NUMBER_RANGES_REGEX, $emphasizeLines)) {
                // Input does not fit the pattern, log a warning
                $this->logger->warning('Invalid value for option emphasize-lines in code-block directive. Expected format: \'1-5, 7, 33\'', $blockContext->getLoggerInformation());
            }
        }

        $node->setEmphasizeLines($emphasizeLines);
    }
}
