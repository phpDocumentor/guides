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

    public function __construct(private string|null $level = LogLevel::WARNING)
    {
    }

    public function hasBeenCalled(): bool
    {
        return $this->hasBeenCalled;
    }

    public function __invoke(array|LogRecord $record): array|LogRecord
    {
        if (strtolower($record['level_name']) === $this->level) {
            $this->hasBeenCalled = true;
        }

        return $record;
    }
}
