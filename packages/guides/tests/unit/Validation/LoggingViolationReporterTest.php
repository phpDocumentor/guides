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

namespace phpDocumentor\Guides\Validation;

use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

final class LoggingViolationReporterTest extends TestCase
{
    private TestHandler $handler;
    private LoggingViolationReporter $violationReporter;

    protected function setUp(): void
    {
        $this->handler = new TestHandler();
        $this->violationReporter = new LoggingViolationReporter(new Logger('app', [$this->handler]));
    }

    public function testWarningIsLoggedWithItsMessageAndContext(): void
    {
        $this->violationReporter->report(
            Violation::warning('rst.code-block.empty', 'The code-block has no content.', ['rst-file' => 'index.rst']),
        );

        $records = $this->handler->getRecords();
        self::assertCount(1, $records);
        self::assertSame(Level::Warning, $records[0]->level);
        self::assertSame('The code-block has no content.', $records[0]->message);
        self::assertSame(['rst-file' => 'index.rst'], $records[0]->context);
    }

    public function testErrorIsLoggedAsError(): void
    {
        $this->violationReporter->report(Violation::error('rst.table.malformed', 'Malformed table'));

        $records = $this->handler->getRecords();
        self::assertCount(1, $records);
        self::assertSame(Level::Error, $records[0]->level);
        self::assertSame('Malformed table', $records[0]->message);
    }
}
