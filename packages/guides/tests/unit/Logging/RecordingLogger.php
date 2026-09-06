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

use Psr\Log\AbstractLogger;
use Stringable;

/** @internal */
final class RecordingLogger extends AbstractLogger
{
    /** @var array<array{level: mixed, message: string, context: mixed[]}> */
    public array $records = [];

    /** @param mixed[] $context */
    public function log(mixed $level, string|Stringable $message, array $context = []): void
    {
        $this->records[] = ['level' => $level, 'message' => (string) $message, 'context' => $context];
    }
}
