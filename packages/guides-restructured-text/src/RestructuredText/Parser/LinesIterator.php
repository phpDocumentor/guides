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

use Iterator;
use OutOfBoundsException;

use function chr;
use function count;
use function explode;
use function max;
use function mb_strpos;
use function preg_replace;
use function sprintf;
use function str_repeat;
use function str_replace;
use function trim;

/** @implements Iterator<string> */
final class LinesIterator implements Iterator
{
    /** @var string[] */
    private array $lines = [];

    private int $position = 0;
    private int $peek = 1;

    public function load(string $document, bool $preserveSpace = false): void
    {
        if (!$preserveSpace) {
            $document = trim($this->prepareDocument($document));
        } else {
            // only remove empty lines at start and end
            $document = preg_replace('/^\n+/', '', $document);
            $document = preg_replace('/\n+$/', '', (string) $document);
        }

        $this->lines = explode("\n", (string) $document);
        $this->rewind();
    }

    public function getNextLine(): string|null
    {
        return $this->lines[$this->position + 1] ?? null;
    }

    /**
     * Moves the lookahead token forward.
     */
    public function peek(): string|null
    {
        if (isset($this->lines[$this->position + $this->peek])) {
            return $this->lines[$this->position + $this->peek++];
        }

        return null;
    }

    public function rewind(): void
    {
        $this->position = 0;
        $this->peek = 1;
    }

    public function current(): string
    {
        if ($this->valid() === false) {
            throw new OutOfBoundsException('Attempted to read a line that does not exist');
        }

        return $this->lines[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    /**
     * @deprecated Work around for Production's eating one line too many
     *
     * @todo Revisit The Loop in {@see DocumentParserContext::parseLines()}
     *          and see if the Look Ahead timing should be done differently
     */
    public function prev(): void
    {
        --$this->position;
    }

    public function next(): void
    {
        ++$this->position;
        $this->peek = 1;
    }

    public function atStart(): bool
    {
        return $this->position === 0;
    }

    public function valid(): bool
    {
        return isset($this->lines[$this->position]);
    }

    private function prepareDocument(string $document): string
    {
        $document = str_replace("\r\n", "\n", $document);
        $document = sprintf("\n%s\n", $document);

        // Removing UTF-8 BOM
        $document = str_replace("\xef\xbb\xbf", '', $document);

        // Replace \u00a0 with " "
        $document = str_replace(chr(194) . chr(160), ' ', $document);

        return $document;
    }

    /** @return string[] */
    public function toArray(): array
    {
        return $this->lines;
    }

    public function isEmpty(): bool
    {
        return count($this->lines) === 0 || (count($this->lines) === 1 && trim($this->lines[0]) === '');
    }

    /** @psalm-assert-if-false non-empty-string $line */
    public static function isEmptyLine(string|null $line): bool
    {
        if ($line === null) {
            return false;
        }

        return trim($line) === '';
    }

    /** @psalm-assert-if-true null $line */
    public static function isNullOrEmptyLine(string|null $line): bool
    {
        if ($line === null) {
            return true;
        }

        return self::isEmptyLine($line);
    }

    /**
     * Is this line "indented"?
     *
     * A blank line also counts as a "block" line, as it
     * may be the empty line between, for example, a
     * ".. note::" directive and the indented content on the
     * next lines.
     *
     * @param int $minIndent can be used to require a specific level of
     *                       indentation for non-blank lines (number of spaces)
     */
    public static function isBlockLine(string|null $line, int $minIndent = 1): bool
    {
        if ($line === null) {
            return false;
        }

        return trim($line) === '' || self::isIndented($line, $minIndent);
    }

    /**
     * Check if line is an indented one.
     *
     * This does *not* include blank lines, use {@see isBlockLine()} to check
     * for blank or indented lines.
     *
     * @param int $minIndent can be used to require a specific level of indentation (number of spaces)
     */
    public static function isIndented(string $line, int $minIndent): bool
    {
        return self::isIndentedBy($line, $minIndent, ' ') || self::isIndentedBy($line, $minIndent, "\t");
    }

    private static function isIndentedBy(string $line, int $minIndent, string $indentationChar): bool
    {
        return mb_strpos($line, str_repeat($indentationChar, max(1, $minIndent))) === 0;
    }
}
