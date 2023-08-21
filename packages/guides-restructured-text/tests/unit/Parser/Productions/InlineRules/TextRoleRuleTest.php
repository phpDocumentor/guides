<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules;

use Generator;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRoleFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TextRoleRuleTest extends TestCase
{
    /** @return Generator<string, string[]> */
    public static function roleFormatProvider(): Generator
    {
        yield 'simple role' => [
            ':role:`content`',
            'role',
            'content',
            'content',
        ];

        yield 'role with domain' => [
            ':domain:role:`content`',
            'domain:role',
            'content',
            'content',
            'domain',
        ];

        yield 'role with escaped backticks' => [
            ':role:`con\`tent`',
            'role',
            'con`tent',
            'con\`tent',
        ];
    }

    #[DataProvider('roleFormatProvider')]
    public function testApplyDoesPassTheRoleAndDomainToFactory(
        string $input,
        string $expectedRole,
        string $expectedContent,
        string $expectedRawContent,
        string|null $expectedDomain = null,
    ): void {
        $textRoleFactory = $this->createMock(TextRoleFactory::class);

        $collectingRole = new class implements TextRole {
            public function getName(): string
            {
                return 'role';
            }

            /** @return string[] */
            public function getAliases(): array
            {
                return [];
            }

            public function processNode(DocumentParserContext $documentParserContext, string $role, string $content, string $rawContent): InlineNode
            {
                return new class ($role, $content, $rawContent) extends InlineNode {
                    public function __construct(
                        public string $role,
                        public string $content,
                        public string $rawContent,
                    ) {
                        parent::__construct('test', $this->content);
                    }
                };
            }
        };

        $textRoleFactory->expects(self::once())
            ->method('getTextRole')
            ->with('role', $expectedDomain)
            ->willReturn($collectingRole);

        $lexer = new InlineLexer();
        $lexer->setInput($input);
        $lexer->moveNext();
        $lexer->moveNext();

        $textRoleRule = new TextRoleRule();
        self::assertTrue($textRoleRule->applies($lexer));
        $documentParserContext = new DocumentParserContext(
            $this->createStub(ParserContext::class),
            $textRoleFactory,
            $this->createStub(MarkupLanguageParser::class),
        );
        $node = $textRoleRule->apply(
            new BlockContext($documentParserContext, ''),
            $lexer,
        );

        /**
         * @psalm-suppress UndefinedPropertyFetch
         * @phpstan-ignore-next-line
         */
        self::assertSame($expectedRole, $node->role);
        /**
         * @psalm-suppress UndefinedPropertyFetch
         * @phpstan-ignore-next-line
         */
        self::assertSame($expectedContent, $node->content);
        /**
         * @psalm-suppress UndefinedPropertyFetch
         * @phpstan-ignore-next-line
         */
        self::assertSame($expectedRawContent, $node->rawContent);
    }
}
