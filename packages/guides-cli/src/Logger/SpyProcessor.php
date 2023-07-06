<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Cli\Logger;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * This decorator has an extra method to check whether anything was logged
 *
 * @internal
 */
final class SpyProcessor implements ProcessorInterface
{
    private bool $hasBeenCalled = false;

    public function hasBeenCalled(): bool
    {
        return $this->hasBeenCalled;
    }

    public function __invoke(array|LogRecord $record): array|LogRecord
    {
        $this->hasBeenCalled = true;

        return $record;
    }
}
