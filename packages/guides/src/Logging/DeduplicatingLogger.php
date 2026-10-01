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
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;

use function serialize;
use function sprintf;

/**
 * Suppresses a repeat of a log message already seen, at a configurable
 * granularity -- see {@see LogDeduplicationLevel}.
 *
 * A single document tree gets rendered once per configured output format
 * (html, tex, interlink, ...), so a warning raised while rendering a node --
 * a broken image reference, an unsupported directive -- fires again,
 * identically, for every format. Parse- and compile-time messages don't
 * have this problem: those phases each run exactly once per invocation
 * regardless of how many output formats are configured. The same problem
 * recurring on different pages is a related but distinct case, since it
 * isn't a byte-identical repeat -- {@see LogDeduplicationLevel::Message}
 * covers that by dropping context (rst-file, line, ...) from the key.
 *
 * @link https://github.com/phpDocumentor/guides/issues/920
 */
final class DeduplicatingLogger extends AbstractLogger
{
    /** @var array<string, true> */
    private array $seen = [];

    private int $suppressedCount = 0;

    private readonly LogDeduplicationLevel $level;

    public function __construct(
        private readonly LoggerInterface $logger,
        LogDeduplicationLevel|string $level = LogDeduplicationLevel::Message,
    ) {
        $this->level = $level instanceof LogDeduplicationLevel ? $level : LogDeduplicationLevel::from($level);
    }

    /** @param mixed[] $context */
    public function log(mixed $level, string|Stringable $message, array $context = []): void
    {
        $key = match ($this->level) {
            LogDeduplicationLevel::None => null,
            LogDeduplicationLevel::Exact => serialize([$level, (string) $message, $context]),
            LogDeduplicationLevel::Message => serialize([$level, (string) $message]),
        };

        if ($key !== null) {
            if (isset($this->seen[$key])) {
                $this->suppressedCount++;

                return;
            }

            $this->seen[$key] = true;
        }

        $this->logger->log($level, $message, $context);
    }

    /**
     * Reports, as one final log line, how many repeat messages this run
     * suppressed -- a per-line count isn't available since a message already
     * written can't be edited once a later repeat of it turns up. A no-op
     * when nothing was suppressed (including when the level is
     * {@see LogDeduplicationLevel::None}, which never suppresses).
     *
     * Call once, after the whole run finishes; calling it again reports
     * only whatever was suppressed since the previous call.
     */
    public function logSuppressedSummary(): void
    {
        if ($this->suppressedCount === 0) {
            return;
        }

        $this->logger->log(
            LogLevel::WARNING,
            sprintf(
                '%d further log message(s) were suppressed as duplicates of an earlier message.',
                $this->suppressedCount,
            ),
        );
        $this->suppressedCount = 0;
    }
}
