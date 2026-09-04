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

namespace phpDocumentor\Guides\RestructuredText\Nodes;

use function is_int;
use function is_string;

/**
 * Where in the source a directive was written, for logging. Built from the
 * project-wide "logger information" convention (BlockContext,
 * DocumentParserContext, ParserContext, DocumentNode, ...), which is a plain
 * array by necessity -- it ultimately feeds PSR-3's LoggerInterface, whose
 * $context parameter is a plain array -- but that leaves a directive with no
 * indication of which keys actually exist. This narrows it to the three keys
 * that convention ever actually sets.
 */
final class DirectiveSourceLocation
{
    public function __construct(
        public readonly string|null $file = null,
        public readonly int|null $lineNumber = null,
        public readonly string|null $currentLine = null,
    ) {
    }

    /** @param array<string, int|string> $loggerInformation */
    public static function fromLoggerInformation(array $loggerInformation): self
    {
        $file = $loggerInformation['rst-file'] ?? null;
        $lineNumber = $loggerInformation['currentLineNumber'] ?? null;
        $currentLine = $loggerInformation['currentLine'] ?? null;

        return new self(
            is_string($file) ? $file : null,
            is_int($lineNumber) ? $lineNumber : null,
            is_string($currentLine) ? $currentLine : null,
        );
    }

    /**
     * The plain array PSR-3's LoggerInterface::warning()/error() expects as
     * $context.
     *
     * @return array<string, int|string>
     */
    public function toLoggerInformation(): array
    {
        $info = [];
        if ($this->file !== null) {
            $info['rst-file'] = $this->file;
        }

        if ($this->lineNumber !== null) {
            $info['currentLineNumber'] = $this->lineNumber;
        }

        if ($this->currentLine !== null) {
            $info['currentLine'] = $this->currentLine;
        }

        return $info;
    }
}
