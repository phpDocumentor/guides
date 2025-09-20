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

namespace phpDocumentor\Guides\RestructuredText\Parser;

use function array_pop;
use function count;
use function implode;
use function ltrim;
use function mb_strlen;
use function min;
use function strlen;
use function substr;
use function trim;

use const PHP_INT_MAX;

final class Buffer
{
    /** @param string[] $lines */
    public function __construct(
        private array $lines = [],
        private UnindentStrategy $unindentStrategy = UnindentStrategy::ALL,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->lines === [];
    }

    public function count(): int
    {
        return count($this->lines);
    }

    public function has(int $key): bool
    {
        return isset($this->lines[$key]);
    }

    public function get(int $key): string
    {
        return $this->lines[$key] ?? '';
    }

    public function push(string $line): void
    {
        $this->lines[] = $line;
    }

    public function set(int $key, string $line): void
    {
        $this->lines[$key] = $line;
    }

    /** @return string[] */
    public function getLines(): array
    {
        $this->unIndent();

        return $this->lines;
    }

    public function getLinesString(): string
    {
        $this->unIndent();

        return implode("\n", $this->lines);
    }

    public function pop(): string|null
    {
        return array_pop($this->lines);
    }

    public function getLastLine(): string|null
    {
        $lastLineKey = count($this->lines) - 1;

        if (!isset($this->lines[$lastLineKey])) {
            return null;
        }

        return $this->lines[$lastLineKey];
    }

    public function clear(): void
    {
        $this->lines = [];
    }

    public function trimLines(): void
    {
        foreach ($this->lines as $i => $line) {
            $this->lines[$i] = trim($line);
        }
    }

    private function unIndent(): void
    {
        if ($this->unindentStrategy === UnindentStrategy::NONE) {
            return;
        }

        $indentation = $this->detectIndentation();
        if ($indentation === 0) {
            return;
        }

        foreach ($this->lines as $i => $line) {
            if (strlen($line) < $indentation) {
                continue;
            }

            $this->lines[$i] = substr($line, $indentation);
        }
    }

    private function detectIndentation(): int
    {
        if (!isset($this->lines[0])) {
            return 0;
        }

        if ($this->unindentStrategy === UnindentStrategy::NONE) {
            return 0;
        }

        if ($this->unindentStrategy === UnindentStrategy::FIRST) {
            return mb_strlen($this->lines[0]) - mb_strlen(ltrim($this->lines[0]));
        }

        $indent = PHP_INT_MAX;

        foreach ($this->lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $indent = min($indent, mb_strlen($line) - mb_strlen(ltrim($line)));
        }

        return $indent;
    }
}
