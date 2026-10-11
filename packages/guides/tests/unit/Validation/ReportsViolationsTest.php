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

final class ReportsViolationsTest extends TestCase
{
    use ReportsViolations;

    public function testViolationIsReportedToAViolationReporter(): void
    {
        $reporter = new class implements ViolationReporter {
            /** @var list<Violation> */
            public array $violations = [];

            public function report(Violation $violation): void
            {
                $this->violations[] = $violation;
            }
        };
        $violation = Violation::warning('rst.code-block.empty', 'The code-block has no content.');

        $this->reportViolation($reporter, $violation);

        self::assertSame([$violation], $reporter->violations);
    }

    public function testViolationIsLoggedToALoggerAsBefore(): void
    {
        $handler = new TestHandler();

        $this->reportViolation(
            new Logger('app', [$handler]),
            Violation::error('rst.table.malformed', 'Malformed table', ['rst-file' => 'index.rst']),
        );

        $records = $handler->getRecords();
        self::assertCount(1, $records);
        self::assertSame(Level::Error, $records[0]->level);
        self::assertSame('Malformed table', $records[0]->message);
        self::assertSame(['rst-file' => 'index.rst'], $records[0]->context);
    }

    public function testViolationIsIgnoredWithoutAReporter(): void
    {
        $this->expectNotToPerformAssertions();

        $this->reportViolation(null, Violation::warning('rst.code-block.empty', 'Ignored'));
    }
}
