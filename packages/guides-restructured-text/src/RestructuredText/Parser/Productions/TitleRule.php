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

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;

use function in_array;
use function strlen;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#sections
 * @implements Rule<TitleNode>
 */
class TitleRule implements Rule
{
    private const HEADER_LETTERS = [
        '!',
        '"',
        '#',
        '$',
        '%',
        '&',
        '\'',
        '(',
        ')',
        '*',
        '+',
        ',',
        '-',
        '.',
        '/',
        ':',
        ';',
        '<',
        '=',
        '>',
        '?',
        '@',
        '[',
        '\\',
        ']',
        '^',
        '_',
        '`',
        '{',
        '|',
        '}',
        '~',
    ];

    private SpanParser $spanParser;

    public function __construct(SpanParser $spanParser)
    {
        $this->spanParser = $spanParser;
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        $line = $documentParser->getDocumentIterator()->current();
        $nextLine = $documentParser->getDocumentIterator()->getNextLine();

        return $this->currentLineIsAnOverline($line, $nextLine)
            || $this->nextLineIsAnUnderline($line, $nextLine);
    }

    public function apply(DocumentParserContext $documentParserContext, ?Node $on = null): ?Node
    {
        $documentIterator = $documentParserContext->getDocumentIterator();
        $title = '';
        $overlineLetter = $this->currentLineIsAnOverline(
            $documentIterator->current(),
            $documentIterator->getNextLine()
        );

        if ($overlineLetter !== '') {
            $documentIterator->next();
            $title = trim($documentIterator->current()); // Title with over and underlines may be indented
        }

        $underlineLetter = $this->nextLineIsAnUnderline($documentIterator->current(), $documentIterator->getNextLine());
        if ($underlineLetter !== '') {
            if (($overlineLetter === '' || $overlineLetter === $underlineLetter)) {
                $title = trim($documentIterator->current()); // Title with over and underlines may be indented
            } else {
                $underlineLetter = '';
            }
        }
        $documentIterator->next();
        $documentIterator->next();

        $context = $documentParserContext->getContext();

        $letter = $overlineLetter ?: $underlineLetter;
        $level = $documentParserContext->getLevel($letter);

        return new TitleNode($this->spanParser->parse($title, $context), $level);
    }

    public function isSpecialLine(string $line): ?string
    {
        if (strlen($line) < 2) {
            return null;
        }

        $letter = $line[0];

        if (!in_array($letter, self::HEADER_LETTERS, true)) {
            return null;
        }

        for ($i = 1; $i < strlen($line); $i++) {
            if ($line[$i] !== $letter) {
                return null;
            }
        }

        return $letter;
    }

    private function currentLineIsAnOverline(string $line, ?string $nextLine): string
    {
        $letter = $this->isSpecialLine($line);
        if ($nextLine !== null && $letter && $this->isTextLine($nextLine)) {
            return $letter;
        }

        return '';
    }

    private function nextLineIsAnUnderline(string $line, ?string $nextLine): string
    {
        $letter = $nextLine !== null ? $this->isSpecialLine($nextLine) : '';

        if ($letter && $this->isTextLine($line)) {
            return $letter;
        }

        return '';
    }

    private function isTextLine(string $line): bool
    {
        return trim($line) !== '';
    }
}
