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

use PHPUnit\Framework\TestCase;

final class CompositeViolationReporterTest extends TestCase
{
    public function testEachReporterReceivesTheViolation(): void
    {
        $violation = Violation::warning('rst.code-block.empty', 'The code-block has no content.');
        $first = $this->createMock(ViolationReporter::class);
        $first->expects(self::once())->method('report')->with($violation);
        $second = $this->createMock(ViolationReporter::class);
        $second->expects(self::once())->method('report')->with($violation);

        (new CompositeViolationReporter([$first, $second]))->report($violation);
    }
}
