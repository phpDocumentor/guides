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

use phpDocumentor\Guides\Nodes\ListItemNode;
use phpDocumentor\Guides\Nodes\ListNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use Webmozart\Assert\Assert;

use function count;
use function ltrim;
use function max;
use function mb_strlen;
use function preg_match;
use function preg_replace;
use function str_repeat;
use function strlen;
use function strpos;
use function substr;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#bullet-lists
 * @implements Rule<ListNode>
 */
final class ListRule implements Rule
{
    /**
     * A regex matching all bullet list markers and a subset of the enumerated list markers.
     *
     * @see https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#bullet-lists
     * @see https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#enumerated-lists
     */
    private const LIST_MARKER = '/
        ^(
            [-+*\x{2022}\x{2023}\x{2043}]     # match bullet list markers: "*", "+", "-", "•", "‣", or "⁃"        
        )
        (?:\s+|$)
         # capture the spaces between marker and text to determine the list item text offset
         # (or eol, if text starts on a new line)
        /ux';

    private RuleContainer $productions;

    public function __construct(RuleContainer $productions)
    {
        $this->productions = $productions;
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        $documentIterator = $documentParser->getDocumentIterator();
        return $this->isListLine($documentIterator->current());
    }

    public function apply(DocumentParserContext $documentParserContext, ?Node $on = null): ?Node
    {
        $documentIterator = $documentParserContext->getDocumentIterator();

        $buffer = new Buffer();
        //First line sets the listmarker of the list, and the indentation of the current item.
        $listConfig = $this->getItemConfig($documentIterator->current());
        $buffer->push($documentIterator->current());

        $items = [];

        while ($this->isListItemStart($documentIterator->getNextLine(), $listConfig['marker'])
                || $this->isBlockLine($documentIterator->getNextLine(), $listConfig['indenting'])
        ) {
            $documentIterator->next();

            if ($this->isListItemStart($documentIterator->current())) {
                $listConfig = $this->getItemConfig($documentIterator->current());
                $items[] = $this->parseListItem($listConfig, $buffer, $documentParserContext);
                $buffer = new Buffer();
            }

            // the list item offset is determined by the offset of the first text.
            // An offset of 1 or lower indicates that the list line didn't contain any text.
            if ($listConfig['indenting'] <= 1) {
                $listConfig['indenting'] = strlen($documentIterator->current()) - strlen(
                    ltrim($documentIterator->current())
                );
            }

            $buffer->push($documentIterator->current());
        }

        $items[] = $this->parseListItem($listConfig, $buffer, $documentParserContext);

        return new ListNode($items, false);
    }

    private function isListLine(?string $line): bool
    {
        if ($line === null) {
            return false;
        }

        $isList = preg_match(self::LIST_MARKER, $line) > 0;
        if (!$isList) {
            return false;
        }

        return true;
    }

    /** @return array{marker: string, indenting: int} */
    public function getItemConfig(string $line): array
    {
        $isList = preg_match(self::LIST_MARKER, $line, $m) > 0;
        if (!$isList) {
            throw new \InvalidArgumentException('Line is not a valid item line');
        }

        return [
            'marker' => $m[1],
            'indenting' => mb_strlen($m[0])
        ];
    }

    private function isListItemStart(?string $line, ?string $listMarker = null): bool
    {
        if ($line === null) {
            return false;
        }

        $isList = preg_match(self::LIST_MARKER, $line, $m) > 0;
        if (!$isList) {
            return false;
        }

        $normalizedMarker = $m[1];

        if ($listMarker !== null) {
            return $normalizedMarker === $listMarker;
        }

        return true;
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
    private function isBlockLine(?string $line, int $minIndent = 1): bool
    {
        if ($line === null) {
            return false;
        }

        return trim($line) === '' || $this->isIndented($line, $minIndent);
    }

    /**
     * Check if line is an indented one.
     *
     * This does *not* include blank lines, use {@see isBlockLine()} to check
     * for blank or indented lines.
     *
     * @param int $minIndent can be used to require a specific level of indentation (number of spaces)
     */
    private function isIndented(string $line, int $minIndent): bool
    {
        return mb_strpos($line, str_repeat(' ', max(1, $minIndent))) === 0;
    }

    /** @param array{marker: string, indenting: int} $listConfig */
    private function parseListItem(array $listConfig, Buffer $buffer, DocumentParserContext $context): ListItemNode
    {
        $normalized = new Buffer();

        foreach ($buffer->getLines() as $line) {
            $normalized->push(mb_substr($line, $listConfig['indenting']));
        }

        $listItem = new ListItemNode($listConfig['marker'], false, []);
        $context = $context->withContents($normalized->getLinesString());
        while ($context->getDocumentIterator()->valid()) {
            $this->productions->apply($context, $listItem);
        }

        $nodes = $listItem->getChildren();
        if (count($nodes) > 1) {
            return $listItem;
        }

        // the list item offset is determined by the offset of the first text
        if ($nodes[0] instanceof ParagraphNode) {
            return new ListItemNode($listConfig['marker'], false, $nodes[0]->getChildren());
        }

        return $listItem;
    }
}
