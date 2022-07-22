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

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\Directive as DirectiveHandler;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LineDataParser;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use Throwable;

use function preg_match;
use function sprintf;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#directives
 */
final class DirectiveRule implements Rule
{
    private LineDataParser $lineDataParser;

    private LiteralBlockRule $literalBlockRule;

    /** @var iterable<DirectiveHandler> */
    private array $directives;

    /**
     * @param iterable<DirectiveHandler> $directives
     */
    public function __construct(
        LineDataParser        $lineDataParser,
        LiteralBlockRule      $literalBlockRule,
        iterable              $directives = []
    ) {
        $this->lineDataParser = $lineDataParser;
        $this->literalBlockRule = $literalBlockRule;
        foreach ($directives as $directive) {
            $this->registerDirective($directive);
        }
    }

    private function registerDirective(DirectiveHandler $directive): void
    {
        $this->directives[$directive->getName()] = $directive;
        foreach ($directive->getAliases() as $alias) {
            $this->directives[$alias] = $directive;
        }
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        return $this->isDirective($documentParser->getDocumentIterator()->current());
    }

    private function isDirective(string $line): bool
    {
        return preg_match('/^\.\. (\|(.+)\| |)([^\s]+)::( (.*)|)$/mUsi', $line) > 0;
    }

    public function apply(DocumentParserContext $documentParserContext, ?Node $on = null): ?Node
    {
        $documentIterator = $documentParserContext->getDocumentIterator();
        $openingLine = $documentIterator->current();
        $documentIterator->next();
        $directive = $this->lineDataParser->parseDirective($openingLine);

        if ($directive === null) {
            return null;
        }

        $directiveHandler = $this->getDirectiveHandler($directive);
        if ($directiveHandler === null) {
            $message = sprintf(
                'Unknown directive: "%s" %sfor line "%s"',
                $directive->getName(),
                $documentParserContext->getContext()->getCurrentFileName() !== '' ? sprintf(
                    'in "%s" ',
                    $documentParserContext->getContext()->getCurrentFileName()
                ) : '',
                $openingLine
            );

            $documentParserContext->getContext()->addError($message);

            return null;
        }

        $this->interpretDirectiveOptions($documentIterator, $directive);

        // Processing the Directive, the handler is responsible for adding the right Nodes to the document.
        try {
            $directiveHandler->process(
                $documentParserContext->getParser(),
                $this->interpretContentBlock($documentParserContext),
                $directive->getVariable(),
                $directive->getData(),
                $directive->getOptions()
            );
        } catch (Throwable $e) {
            $message = sprintf(
                'Error while processing "%s" directive%s: %s',
                $directiveHandler->getName(),
                $documentParserContext->getContext()->getCurrentFileName() !== '' ? sprintf(
                    ' in "%s"',
                    $documentParserContext->getContext()->getCurrentFileName()
                ) : '',
                $e->getMessage()
            );

            $documentParserContext->getContext()->addError($message);
        }

        return null;
    }

    private function getDirectiveHandler(Directive $directive): ?DirectiveHandler
    {
        return $this->directives[$directive->getName()] ?? null;
    }

    private function interpretDirectiveOptions(LinesIterator $documentIterator, Directive $directive): void
    {
        while ($documentIterator->valid()
            && ($directiveOption = $this->lineDataParser->parseDirectiveOption($documentIterator->current())) !== null
        ) {
            $directive->setOption($directiveOption->getName(), $directiveOption->getValue());

            $documentIterator->next();
        }
    }

    private function interpretContentBlock(DocumentParserContext $documentParserContext): ?Node
    {
        $contentBlock = null;
        $documentIterator = $documentParserContext->getDocumentIterator();
        $documentParserContext->nextIndentedBlockShouldBeALiteralBlock = true;
        if ($documentIterator->valid() && $this->literalBlockRule->applies($documentParserContext)) {
            $contentBlock = $this->literalBlockRule->apply($documentParserContext);
        }

        return $contentBlock;
    }
}
