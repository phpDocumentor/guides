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
        if (trim($documentIterator->current()) !== $listConfig['marker']) {
            $buffer->push(mb_substr($documentIterator->current(), $listConfig['indenting']));
        }

        $items = [];

        while ($this->isListItemStart($documentIterator->getNextLine(), $listConfig['marker'])
                || LinesIterator::isBlockLine($documentIterator->getNextLine(), $listConfig['indenting'])
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

            if (trim($documentIterator->current()) !== $listConfig['marker']) {
                $buffer->push(mb_substr($documentIterator->current(), $listConfig['indenting']));
            }
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
            'indenting' => $m[0] === $line ? 1 : mb_strlen($m[0])
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

    /** @param array{marker: string, indenting: int} $listConfig */
    private function parseListItem(array $listConfig, Buffer $buffer, DocumentParserContext $context): ListItemNode
    {


        $listItem = new ListItemNode($listConfig['marker'], false, []);
        $context = $context->withContents($buffer->getLinesString());
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
