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

    /** @var array<InlineLexer::*, CachableInlineRule> */
    private array $cache = [];

    /** @param iterable<InlineRule> $inlineRules */
    public function __construct(iterable $inlineRules)
    {
        $this->rules = array_filter([...$inlineRules], static fn ($rule) => $rule instanceof CachableInlineRule === false);
        usort($this->rules, static fn (InlineRule $a, InlineRule $b): int => $a->getPriority() > $b->getPriority() ? -1 : 1);
        foreach ($inlineRules as $rule) {
            if (!($rule instanceof CachableInlineRule)) {
                continue;
            }

            $this->cache[$rule->getToken()] = $rule;
        }
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
