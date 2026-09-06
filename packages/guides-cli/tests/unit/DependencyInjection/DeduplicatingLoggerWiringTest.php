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

namespace phpDocumentor\Guides\Cli\DependencyInjection;

use Monolog\Handler\TestHandler;
use phpDocumentor\Guides\ApplicationTestCase;
use phpDocumentor\Guides\Logging\DeduplicatingLogger;
use Psr\Log\LoggerInterface;

use function assert;

final class DeduplicatingLoggerWiringTest extends ApplicationTestCase
{
    public function testLoggerInterfaceResolvesToDeduplicatingLogger(): void
    {
        self::assertInstanceOf(DeduplicatingLogger::class, $this->getContainer()->get(LoggerInterface::class));
    }

    public function testSameWarningAcrossFormatsAndPagesIsLoggedOnceThroughTheWiredContainer(): void
    {
        $logger = $this->getContainer()->get(LoggerInterface::class);
        assert($logger instanceof LoggerInterface);

        $testHandler = $this->getContainer()->get(TestHandler::class);
        assert($testHandler instanceof TestHandler);

        // Simulates the same render-phase warning firing once per configured
        // output format (html, tex, ...) and, separately, the same warning
        // recurring on different pages -- both are what issue #920 reports.
        $logger->warning('Broken image reference', ['rst-file' => 'index.rst']);
        $logger->warning('Broken image reference', ['rst-file' => 'page1.rst']);
        $logger->warning('Broken image reference', ['rst-file' => 'page2.rst']);

        self::assertCount(1, $testHandler->getRecords());
    }
}
