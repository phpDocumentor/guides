<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use Exception;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\InlineRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\MatchCachable;

use function usort;

class InlineParser
{
    /** @var InlineRule[] */
    private array $rules;
    private $lexer;

    /** @var InlineRule[] */
    private array $cachedRules = [];

    /** @param iterable<InlineRule> $inlineRules */
    public function __construct(iterable $inlineRules)
    {
        $this->lexer = new InlineLexer();
        $this->rules = [...$inlineRules];
        usort($this->rules, static fn (InlineRule $a, InlineRule $b): int => $a->getPriority() > $b->getPriority() ? -1 : 1);
    }

    public function parse(string $content, BlockContext $blockContext): InlineCompoundNode
    {
        $this->lexer->setInput($content);
        $this->lexer->moveNext();
        $this->lexer->moveNext();
        $nodes = [];
        $previous = null;
        while ($this->lexer->token !== null) {
            if (isset($this->cachedRules[$this->lexer->token->type])) {
                $node = $this->cachedRules[$this->lexer->token->type]->apply($blockContext, $this->lexer);
                if ($node !== null) {
                    $nodes[] = $node;
                    $previous = $node;
                    continue;
                }
            }

            foreach ($this->rules as $key => $inlineRule) {
                $node = null;
                if ($inlineRule->applies($this->lexer)) {
                    if ($inlineRule instanceof MatchCachable && $inlineRule->isCacheable()) {
                        $this->cachedRules[$this->lexer->token->type] = $inlineRule;
                        unset($this->rules[$key]);
                    }

                    $node = $inlineRule->apply($blockContext, $this->lexer);
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
