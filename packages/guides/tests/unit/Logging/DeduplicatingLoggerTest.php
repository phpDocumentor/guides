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

namespace phpDocumentor\Guides\Logging;

use PHPUnit\Framework\TestCase;

final class DeduplicatingLoggerTest extends TestCase
{
    public function testDefaultLevelIsMessage(): void
    {
        $inner = new RecordingLogger();
        $logger = new DeduplicatingLogger($inner);

        $logger->warning('Broken image reference', ['rst-file' => 'index.rst']);
        $logger->warning('Broken image reference', ['rst-file' => 'other.rst']);

        self::assertCount(1, $inner->records);
    }

    public function testLevelAcceptsTheEnumsRawStringValue(): void
    {
        $inner = new RecordingLogger();
        $logger = new DeduplicatingLogger($inner, 'exact');

        $logger->warning('Broken image reference', ['rst-file' => 'index.rst']);
        $logger->warning('Broken image reference', ['rst-file' => 'other.rst']);

        self::assertCount(2, $inner->records);
    }

    public function testMessageLevelIgnoresContext(): void
    {
        $inner = new RecordingLogger();
        $logger = new DeduplicatingLogger($inner, LogDeduplicationLevel::Message);

        $logger->warning('Broken image reference', ['rst-file' => 'index.rst']);
        $logger->warning('Broken image reference', ['rst-file' => 'page1.rst']);
        $logger->warning('Broken image reference', ['rst-file' => 'page2.rst']);

        self::assertCount(1, $inner->records);
        // The first occurrence's context is what gets kept -- not merged or
        // replaced by later, suppressed occurrences.
        self::assertSame(['rst-file' => 'index.rst'], $inner->records[0]['context']);
    }

    public function testMessageLevelStillDistinguishesDifferentMessages(): void
    {
        $inner = new RecordingLogger();
        $logger = new DeduplicatingLogger($inner, LogDeduplicationLevel::Message);

        $logger->warning('Broken image reference "a.png"', ['rst-file' => 'index.rst']);
        $logger->warning('Broken image reference "b.png"', ['rst-file' => 'index.rst']);

        self::assertCount(2, $inner->records);
    }

    public function testMessageLevelStillDistinguishesDifferentLevels(): void
    {
        $inner = new RecordingLogger();
        $logger = new DeduplicatingLogger($inner, LogDeduplicationLevel::Message);

        $logger->warning('Broken image reference', ['rst-file' => 'index.rst']);
        $logger->error('Broken image reference', ['rst-file' => 'index.rst']);

        self::assertCount(2, $inner->records);
    }

    public function testExactLevelRequiresIdenticalContextToo(): void
    {
        $inner = new RecordingLogger();
        $logger = new DeduplicatingLogger($inner, LogDeduplicationLevel::Exact);

        $logger->warning('Broken image reference', ['rst-file' => 'index.rst']);
        $logger->warning('Broken image reference', ['rst-file' => 'index.rst']);
        $logger->warning('Broken image reference', ['rst-file' => 'other.rst']);

        self::assertCount(2, $inner->records);
    }

    public function testNoneLevelNeverDeduplicates(): void
    {
        $inner = new RecordingLogger();
        $logger = new DeduplicatingLogger($inner, LogDeduplicationLevel::None);

        $logger->warning('Broken image reference', ['rst-file' => 'index.rst']);
        $logger->warning('Broken image reference', ['rst-file' => 'index.rst']);

        self::assertCount(2, $inner->records);
    }

    public function testSuppressedSummaryReportsHowManyRepeatsWereDropped(): void
    {
        $inner = new RecordingLogger();
        $logger = new DeduplicatingLogger($inner, LogDeduplicationLevel::Message);

        $logger->warning('Broken image reference', ['rst-file' => 'index.rst']);
        $logger->warning('Broken image reference', ['rst-file' => 'page1.rst']);
        $logger->warning('Broken image reference', ['rst-file' => 'page2.rst']);
        $logger->logSuppressedSummary();

        self::assertCount(2, $inner->records);
        self::assertSame(
            '2 further log message(s) were suppressed as duplicates of an earlier message.',
            $inner->records[1]['message'],
        );
    }

    public function testSuppressedSummaryIsANoOpWhenNothingWasSuppressed(): void
    {
        $inner = new RecordingLogger();
        $logger = new DeduplicatingLogger($inner, LogDeduplicationLevel::Message);

        $logger->warning('Broken image reference', ['rst-file' => 'index.rst']);
        $logger->logSuppressedSummary();

        self::assertCount(1, $inner->records);
    }

    public function testSuppressedSummaryOnlyCoversWhatHappenedSinceThePreviousCall(): void
    {
        $inner = new RecordingLogger();
        $logger = new DeduplicatingLogger($inner, LogDeduplicationLevel::Message);

        $logger->warning('Broken image reference', ['rst-file' => 'index.rst']);
        $logger->warning('Broken image reference', ['rst-file' => 'page1.rst']);
        $logger->logSuppressedSummary();
        $logger->logSuppressedSummary();

        self::assertCount(2, $inner->records);
    }
}
