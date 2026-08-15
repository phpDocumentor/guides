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

use phpDocumentor\Guides\Nodes\Inline\InlineNodeInterface;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\InlineRule;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRoleFactory;
use PHPUnit\Framework\TestCase;

final class InlineParserTest extends TestCase
{
    /**
     * A rule may parse content of its own while it applies — a text role rendering its argument, for
     * example. The nested parse used to call setInput() on the very lexer the outer parse was reading
     * from, which discarded the outer token stream and made a later rollback index positions that no
     * longer existed.
     */
    public function testARuleMayParseWhileTheOuterParseIsStillRunning(): void
    {
        $nesting = new class implements InlineRule {
            public InlineParser|null $parser = null;

            private bool $fired = false;

            public function applies(InlineLexer $lexer): bool
            {
                return !$this->fired;
            }

            public function apply(BlockContext $blockContext, InlineLexer $lexer): InlineNodeInterface|null
            {
                $this->fired = true;
                $this->parser?->parse('inner content here', $blockContext);
                $lexer->moveNext();

                return new PlainTextInlineNode('[nested]');
            }

            public function getPriority(): int
            {
                return 1000;
            }
        };

        // Consumes whatever the nesting rule does not, so the parser always finds a rule that applies.
        $plainText = new class implements InlineRule {
            public function applies(InlineLexer $lexer): bool
            {
                return true;
            }

            public function apply(BlockContext $blockContext, InlineLexer $lexer): InlineNodeInterface|null
            {
                $value = (string) ($lexer->token?->value ?? '');
                $lexer->moveNext();

                return new PlainTextInlineNode($value);
            }

            public function getPriority(): int
            {
                return 0;
            }
        };

        $parser = new InlineParser([$nesting, $plainText]);
        $nesting->parser = $parser;

        $node = $parser->parse('before nest after', $this->blockContext());

        // The nesting rule consumes the first token and emits its marker, the nested parse runs, and the
        // outer parse then continues over the rest of its own token stream.
        self::assertSame('[nested]efore nest after', $node->toString());
    }

    private function blockContext(): BlockContext
    {
        $documentParserContext = new DocumentParserContext(
            self::createStub(ParserContext::class),
            self::createStub(TextRoleFactory::class),
            self::createStub(MarkupLanguageParser::class),
        );

        return new BlockContext($documentParserContext, '');
    }
}
