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

namespace phpDocumentor\Guides\Cli\Logger;

use DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

final class SpyProcessorTest extends TestCase
{
    public function testHasBeenCalledReturnsFalseByDefault(): void
    {
        $spyProcessor = new SpyProcessor();

        self::assertFalse($spyProcessor->hasBeenCalled());
    }

    public function testItKnowsWhenALogIsEmitted(): void
    {
        $process = new SpyProcessor();
        $process(new LogRecord(new DateTimeImmutable(), 'test', Level::Warning, 'test message'));
        self::assertTrue($process->hasBeenCalled());
    }

    public function testItKnowsWhenAErrorIsEmitted(): void
    {
        $process = new SpyProcessor();
        $process(new LogRecord(new DateTimeImmutable(), 'test', Level::Error, 'test message'));
        self::assertTrue($process->hasBeenCalled());
    }

    public function testIsNotCalledWhenLevelIsTolow(): void
    {
        $process = new SpyProcessor(LogLevel::ERROR);
        $process(new LogRecord(new DateTimeImmutable(), 'test', Level::Warning, 'test message'));
        self::assertFalse($process->hasBeenCalled());
    }
}
