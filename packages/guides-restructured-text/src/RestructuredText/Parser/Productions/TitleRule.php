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

use InvalidArgumentException;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SectionBeginNode;
use phpDocumentor\Guides\Nodes\SectionEndNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;

use function array_search;
use function in_array;
use function strlen;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#sections
 *
 * @todo convert the TitleRule into a separate SectionRule that can nest itself and close itself when a lower-level
 *       title is encountered
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
        $node = null;
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
                $documentIterator->next();
            } else {
                $underlineLetter = '';
            }
        }

        $context = $documentParserContext->getContext();

        $letter = $overlineLetter ?: $underlineLetter;
        $level = $context->getLevel($letter);
        $level = $context->getInitialHeaderLevel() + $level - 1;

        return new TitleNode($this->spanParser->parse($title, $context), $level);

        //$this->transitionBetweenSections($documentParserContext, $node, $on);

        return $node;
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

    private function transitionBetweenSections(
        DocumentParserContext $documentParserContext,
        TitleNode $node,
        DocumentNode $on
    ): void {
        // TODO: Is this a Title parser, or actually a Section parser? :thinking_face:
        if ($documentParserContext->lastTitleNode !== null) {
            // current level is less than previous so we need to end all open sections
            if ($node->getLevel() < $documentParserContext->lastTitleNode->getLevel()) {
                foreach ($documentParserContext->openSectionsAsTitleNodes as $titleNode) {
                    $this->endOpenSection($documentParserContext, $titleNode, $on);
                }

                // same level as the last so just close the last open section
            } elseif ($node->getLevel() === $documentParserContext->lastTitleNode->getLevel()) {
                $this->endOpenSection($documentParserContext, $documentParserContext->lastTitleNode, $on);
            }
        }

        $this->beginOpenSection($documentParserContext, $node, $on);
    }

    private function beginOpenSection(
        DocumentParserContext $documentParserContext,
        TitleNode $node,
        DocumentNode $on
    ): void {
        $documentParserContext->lastTitleNode = $node;
        $on->addNode(new SectionBeginNode($node));
        $documentParserContext->openSectionsAsTitleNodes->append($node);
    }

    private function endOpenSection(
        DocumentParserContext $documentParserContext,
        TitleNode $titleNode,
        DocumentNode $on
    ): void {
        $on->addNode(new SectionEndNode($titleNode));

        $key = array_search($titleNode, $documentParserContext->openSectionsAsTitleNodes->getArrayCopy(), true);

        if ($key === false) {
            return;
        }

        unset($documentParserContext->openSectionsAsTitleNodes[$key]);
    }
}
