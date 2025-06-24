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

use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Psr\Log\LogLevel;

use function strtolower;

/**
 * This decorator has an extra method to check whether anything was logged
 *
 * @internal
 */
final class SpyProcessor implements ProcessorInterface
{
    private bool $hasBeenCalled = false;
    private Level $level;

    /** @param LogLevel::* $level */
    public function __construct(string|null $level = LogLevel::WARNING)
    {
        if ($level === null) {
            $level = LogLevel::WARNING;
        }

        $this->level = Level::fromName(strtolower($level));
    }

    public function hasBeenCalled(): bool
    {
        return $this->hasBeenCalled;
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        if ($this->level->includes($record->level)) {
            $this->hasBeenCalled = true;
        }

        return $record;
    }
}
