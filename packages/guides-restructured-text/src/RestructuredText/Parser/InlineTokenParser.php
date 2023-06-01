<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use Exception;
use phpDocumentor\Guides\Nodes\InlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\PlainTextToken;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\InlineRule;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;

use function usort;

class InlineTokenParser
{
    /** @var InlineRule[] */
    private array $rules;

    /** @param iterable<InlineRule> $inlineRules */
    public function __construct(iterable $inlineRules)
    {
        $this->rules = [...$inlineRules];
        usort($this->rules, static function (InlineRule $a, InlineRule $b): int {
            return $a->getPriority() > $b->getPriority() ? -1 : 1;
        });
    }

    public function parse(string $content, ParserContext $parserContext): InlineNode
    {
        $lexer = new SpanLexer();
        $lexer->setInput($content);
        $lexer->moveNext();
        $lexer->moveNext();
        $nodes = [];
        $previous = null;
        while ($lexer->token !== null) {
            foreach ($this->rules as $inlineRule) {
                $node = null;
                if ($inlineRule->applies($lexer)) {
                    $node = $inlineRule->apply($parserContext, $lexer);
                }

                if ($node === null) {
                    continue;
                }

                if ($previous instanceof PlainTextToken && $node instanceof PlainTextToken) {
                    $previous->append($node);
                } else {
                    $nodes[] = $node;
                    $previous = $node;
                }

                continue 2;
            }

            throw new Exception('No inline token rule applied.');
        }

        return new InlineNode($nodes);
    }
}
