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

use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolver;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineParser;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRoleFactory;
use PHPUnit\Framework\TestCase;

abstract class RuleTestCase extends TestCase
{
    protected static function assertRemainingEquals(string $expected, LinesIterator $actual): void
    {
        $rest = '';
        $actual->next();

        while ($actual->valid()) {
            $rest .= $actual->current() . "\n";
            $actual->next();
        }

        self::assertEquals($expected, $rest);
    }

    protected function createContext(string $input): BlockContext
    {
        $parserContext = new ParserContext(
            new ProjectNode(),
            'test',
            'test',
            1,
            self::createStub(FilesystemInterface::class),
            new DocumentNameResolver(),
        );
        $documentParserContext = new DocumentParserContext(
            $parserContext,
            self::createStub(TextRoleFactory::class),
            self::createStub(MarkupLanguageParser::class),
        );

        return new BlockContext($documentParserContext, $input);
    }

    protected function givenInlineMarkupRule(): InlineMarkupRule
    {
        $inlineTokenParser = $this->createMock(InlineParser::class);
        $inlineTokenParser->method('parse')->willReturnCallback(
            static fn (string $arg): InlineCompoundNode => new InlineCompoundNode([
                new PlainTextInlineNode($arg),
            ]),
        );

        return new InlineMarkupRule($inlineTokenParser);
    }

    protected function givenCollectAllRuleContainer(): RuleContainer
    {
        return new RuleContainer(new CollectAllRule());
    }
}
