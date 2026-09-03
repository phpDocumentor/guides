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

namespace phpDocumentor\Guides\RestructuredText\Directives;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use phpDocumentor\Guides\Nodes\Index\IndexEntryType;
use phpDocumentor\Guides\Nodes\Index\IndexNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\RuleTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class IndexDirectiveTest extends RuleTestCase
{
    private TestHandler $logHandler;
    private IndexDirective $directive;

    protected function setUp(): void
    {
        $this->logHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($this->logHandler);
        $this->directive = new IndexDirective($logger);
    }

    #[DataProvider('typoProvider')]
    public function testLikelyTypoLogsWarning(string $typo, string $suggestion): void
    {
        $node = $this->directive->process(
            $this->createContext(''),
            new Directive('', 'index', $typo . ': foo'),
        );

        self::assertInstanceOf(IndexNode::class, $node);
        self::assertCount(1, $node->getEntries());
        self::assertSame(IndexEntryType::Single, $node->getEntries()[0]->getType());

        self::assertTrue($this->logHandler->hasWarningThatContains(
            'not a known entry type, did you mean "' . $suggestion . ':"?',
        ));
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function typoProvider(): array
    {
        return [
            'single letter dropped' => ['singl', 'single'],
            'transposed letters' => ['sindle', 'single'],
            'extra letter' => ['pairr', 'pair'],
        ];
    }

    public function testUnrelatedLiteralColonDoesNotLogAnything(): void
    {
        $node = $this->directive->process(
            $this->createContext(''),
            new Directive('', 'index', 'ext:core'),
        );

        self::assertInstanceOf(IndexNode::class, $node);
        self::assertFalse($this->logHandler->hasWarningRecords());
    }

    public function testKnownEntryTypeDoesNotLogAnything(): void
    {
        $node = $this->directive->process(
            $this->createContext(''),
            new Directive('', 'index', 'single: foo'),
        );

        self::assertInstanceOf(IndexNode::class, $node);
        self::assertFalse($this->logHandler->hasWarningRecords());
    }
}
