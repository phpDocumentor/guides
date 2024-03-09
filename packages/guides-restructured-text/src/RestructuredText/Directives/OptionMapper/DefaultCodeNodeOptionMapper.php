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

namespace phpDocumentor\Guides\RestructuredText\Directives\OptionMapper;

use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;
use phpDocumentor\Guides\RestructuredText\Parser\InlineParser;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkupRule;
use Psr\Log\LoggerInterface;

use function preg_match;
use function sprintf;
use function strval;
use function trim;

/**
 * The directives `code-block` and `literalinclude` both create a CodeNode with the same possible options.
 * This common mapper is used by both Directives.
 */
final class DefaultCodeNodeOptionMapper implements CodeNodeOptionMapper
{
    /** @see https://regex101.com/r/czvfnV/2 */
    public const LINE_NUMBER_RANGES_REGEX = '/^\d+(-\d*)?(?:,\s*\d+(-\d*)?)*$/';

    public function __construct(
        private readonly LoggerInterface $logger,
        protected InlineMarkupRule $startingRule,
        private readonly InlineParser $inlineParser,
    ) {
    }

    /** @param DirectiveOption[] $directiveOptions */
    public function apply(
        CodeNode $codeNode,
        array $directiveOptions,
        BlockContext $blockContext,
    ): void {
        if (isset($directiveOptions['language'])) {
            $codeNode->setLanguage(trim((string) $directiveOptions['language']->getValue()));
        }

        $this->setStartingLineNumberBasedOnOptions($directiveOptions, $codeNode);
        $this->setCaptionBasedOnOptions($directiveOptions, $codeNode, $blockContext);
        $this->setEmphasizeLinesBasedOnOptions($directiveOptions, $codeNode, $blockContext);

        $this->setStartingLineNumberBasedOnOptions($directiveOptions, $codeNode);
    }

    /** @param DirectiveOption[] $options */
    private function setCaptionBasedOnOptions(
        array $options,
        CodeNode $node,
        BlockContext $blockContext,
    ): void {
        $caption = null;
        if (isset($options['caption'])) {
            $caption = $this->inlineParser->parse(strval($options['caption']->getValue()), $blockContext);
        }

        $node->setCaption($caption);
    }

    /** @param DirectiveOption[] $options */
    private function setEmphasizeLinesBasedOnOptions(array $options, CodeNode $node, BlockContext $blockContext): void
    {
        $emphasizeLines = null;
        if (isset($options['emphasize-lines'])) {
            $emphasizeLines = (string) $options['emphasize-lines']->getValue();
            if (!preg_match(self::LINE_NUMBER_RANGES_REGEX, $emphasizeLines)) {
                // Input does not fit the pattern, log a warning
                $this->logger->warning(
                    sprintf('Invalid value for option emphasize-lines: "%s". Expected format: \'1-5, 7, 33\'', $emphasizeLines),
                    $blockContext->getLoggerInformation(),
                );
            }
        }

        $node->setEmphasizeLines($emphasizeLines);
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
}
