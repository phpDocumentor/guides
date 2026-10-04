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

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\DataProvider;

final class CommentRuleTest extends RuleTestCase
{
    private CommentRule $rule;
    private TestHandler $logHandler;

    public function setUp(): void
    {
        $this->logHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($this->logHandler);
        $this->rule = new CommentRule($logger);
    }

    #[DataProvider('simpleCommentProvider')]
    public function testCommentApplies(string $input): void
    {
        $context = $this->createContext($input);
        self::assertTrue($this->rule->applies($context));
    }

    /** @return array<array<string>> */
    public static function simpleCommentProvider(): array
    {
        return [
            ['.. Testing comment'],
            ['..'],
        ];
    }

    #[DataProvider('directiveWithoutSpaceProvider')]
    public function testDirectiveWithoutSpaceWarns(string $input, string $expectedMessage): void
    {
        $context = $this->createContext($input);
        self::assertTrue($this->rule->applies($context));

        $this->rule->apply($context);

        $records = $this->logHandler->getRecords();
        self::assertCount(1, $records);
        self::assertSame($expectedMessage, $records[0]->message);
    }

    /** @return array<string, array{string, string}> */
    public static function directiveWithoutSpaceProvider(): array
    {
        return [
            'directive with argument' => [
                '..  confval::passwordPolicies',
                'The comment "confval::passwordPolicies" looks like a directive without a space after "::"; it is ignored. Write "..  confval:: passwordPolicies".',
            ],
            'name with hyphen' => [
                '..  code-block::php',
                'The comment "code-block::php" looks like a directive without a space after "::"; it is ignored. Write "..  code-block:: php".',
            ],
            'argument with spaces' => [
                '..  index::Exceptions; ProductionExceptionHandler',
                'The comment "index::Exceptions; ProductionExceptionHandler" looks like a directive without a space after "::"; it is ignored. Write "..  index:: Exceptions; ProductionExceptionHandler".',
            ],
            'argument with colon' => [
                '..  index::triple:PSR-14 event; TCA; AfterTcaCompilationEvent;',
                'The comment "index::triple:PSR-14 event; TCA; AfterTcaCompilationEvent;" looks like a directive without a space after "::"; it is ignored. Write "..  index:: triple:PSR-14 event; TCA; AfterTcaCompilationEvent;".',
            ],
            'domain directive' => [
                '.. php:method::foo',
                'The comment "php:method::foo" looks like a directive without a space after "::"; it is ignored. Write ".. php:method:: foo".',
            ],
        ];
    }

    public function testEachOfConsecutiveCommentsIsChecked(): void
    {
        $context = $this->createContext(<<<'RST'
..  code-block::php

    $foo = 'bar';

..  This is a note: see below

..  index::Exceptions; ProductionExceptionHandler
RST);

        $this->rule->apply($context);

        $records = $this->logHandler->getRecords();
        self::assertCount(2, $records);
        self::assertStringStartsWith('The comment "code-block::php"', $records[0]->message);
        self::assertStringStartsWith('The comment "index::Exceptions; ProductionExceptionHandler"', $records[1]->message);
    }

    #[DataProvider('commentWithoutWarningProvider')]
    public function testCommentDoesNotWarn(string $input): void
    {
        $context = $this->createContext($input);
        self::assertTrue($this->rule->applies($context));

        $this->rule->apply($context);

        self::assertSame([], $this->logHandler->getRecords());
    }

    /** @return array<string, array{string}> */
    public static function commentWithoutWarningProvider(): array
    {
        return [
            'comment with colon' => ['..  This is a note: see below'],
            'comment mentioning a directive' => ['.. Use the confval:: directive here'],
            'empty comment' => ['..'],
            'double colon later in the comment' => ['.. see Foo::bar() for details'],
        ];
    }
}
