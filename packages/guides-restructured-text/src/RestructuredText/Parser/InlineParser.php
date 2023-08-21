<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use Exception;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\InlineRule;

use function usort;

class InlineParser
{
    /** @var InlineRule[] */
    private array $rules;

    /** @param iterable<InlineRule> $inlineRules */
    public function __construct(iterable $inlineRules)
    {
        $this->rules = [...$inlineRules];
        usort($this->rules, static fn (InlineRule $a, InlineRule $b): int => $a->getPriority() > $b->getPriority() ? -1 : 1);
    }

    public function parse(string $content, BlockContext $blockContext): InlineCompoundNode
    {
        $lexer = new InlineLexer();
        $lexer->setInput($content);
        $lexer->moveNext();
        $lexer->moveNext();
        $nodes = [];
        $previous = null;
        while ($lexer->token !== null) {
            foreach ($this->rules as $inlineRule) {
                $node = null;
                if ($inlineRule->applies($lexer)) {
                    $node = $inlineRule->apply($blockContext, $lexer);
                }

                if ($node === null) {
                    continue;
                }

                if ($previous instanceof PlainTextInlineNode && $node instanceof PlainTextInlineNode) {
                    $previous->append($node);
                } else {
                    $nodes[] = $node;
                    $previous = $node;
                }

                continue 2;
            }

            throw new Exception('No inline token rule applied.');
        }

        return new InlineCompoundNode($nodes);
    }
}
