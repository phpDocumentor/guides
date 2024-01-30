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

namespace phpDocumentor\Guides\Nodes;

use function implode;

final class CodeNode extends TextNode
{
    /** @var int|null The line number to start counting from and display, or null to hide line numbers */
    private int|null $startingLineNumber = null;

    private InlineCompoundNode|null $caption = null;

    private string|null $emphasizeLines = null;

    /** @param string[] $lines */
    public function __construct(array $lines, protected string|null $language = null)
    {
        parent::__construct(implode("\n", $lines));
    }

    public function setLanguage(string|null $language = null): void
    {
        $this->language = $language;
    }

    public function getLanguage(): string|null
    {
        return $this->language;
    }

    public function setStartingLineNumber(int|null $lineNumber): void
    {
        $this->startingLineNumber = $lineNumber;
    }

    public function getStartingLineNumber(): int|null
    {
        return $this->startingLineNumber;
    }

    public function getCaption(): InlineCompoundNode|null
    {
        return $this->caption;
    }

    public function setCaption(InlineCompoundNode|null $caption): void
    {
        $this->caption = $caption;
    }

    public function getEmphasizeLines(): string|null
    {
        return $this->emphasizeLines;
    }

    public function setEmphasizeLines(string|null $emphasizeLines): void
    {
        $this->emphasizeLines = $emphasizeLines;
    }
}
