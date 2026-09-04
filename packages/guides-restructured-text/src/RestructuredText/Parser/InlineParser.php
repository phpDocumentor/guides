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

namespace phpDocumentor\Guides\RestructuredText\Parser;

use Exception;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\CachableInlineRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\InlineRule;

use function array_filter;
use function array_key_exists;
use function usort;

/** @internal */
class InlineParser
{
    /** @var InlineRule[] */
    private array $rules;

    /** @var array<int, CachableInlineRule> */
    private array $cache = [];

    /**
     * Reusable lexer instance, to avoid allocating one per parse.
     *
     * It carries the state of exactly one parse, so it can only be handed to one parse at a time;
     * a nested parse gets its own. See parse().
     */
    private InlineLexer $lexer;

    private bool $lexerInUse = false;

    /** @param iterable<InlineRule> $inlineRules */
    public function __construct(
        iterable $inlineRules,
        private readonly bool $disableLegacyTilde = false,
    ) {
        $this->rules = array_filter([...$inlineRules], static fn ($rule) => $rule instanceof CachableInlineRule === false);
        usort($this->rules, static fn (InlineRule $a, InlineRule $b): int => $a->getPriority() > $b->getPriority() ? -1 : 1);
        foreach ($inlineRules as $rule) {
            if (!($rule instanceof CachableInlineRule)) {
                continue;
            }

            $this->cache[$rule->getToken()] = $rule;
        }

        $this->lexer = new InlineLexer($this->disableLegacyTilde);
    }

    public function parse(string $content, BlockContext $blockContext): InlineCompoundNode
    {
        // A rule may call back into parse() while it applies — a text role parsing its own content,
        // for instance — and the nested call must not take the token stream away from the outer one:
        // setInput() would replace it, and the outer rollback would then index positions that no
        // longer exist. The shared instance is therefore only handed out while it is free.
        $sharedLexerIsFree = !$this->lexerInUse;
        $lexer = $sharedLexerIsFree ? $this->lexer : new InlineLexer($this->disableLegacyTilde);
        $this->lexerInUse = true;

        try {
            return $this->parseWithLexer($lexer, $content, $blockContext);
        } finally {
            if ($sharedLexerIsFree) {
                $this->lexerInUse = false;
            }
        }
    }

    private function parseWithLexer(InlineLexer $lexer, string $content, BlockContext $blockContext): InlineCompoundNode
    {
        $lexer->setInput($content);
        $lexer->moveNext();
        $lexer->moveNext();
        $nodes = [];
        $previous = null;
        while ($lexer->token !== null) {
            foreach ($this->rules as $inlineRule) {
                $node = null;
                if (array_key_exists($lexer->token->type ?? -1, $this->cache)) {
                    $node = $this->cache[$lexer->token->type]->apply($blockContext, $lexer);
                } elseif ($inlineRule->applies($lexer)) {
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
