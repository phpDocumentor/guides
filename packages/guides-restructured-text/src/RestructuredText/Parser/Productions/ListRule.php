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
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\ListItemNode;
use phpDocumentor\Guides\Nodes\ListNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use Psr\Log\LoggerInterface;

use function count;
use function ltrim;
use function mb_strlen;
use function mb_substr;
use function preg_match;
use function strlen;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#bullet-lists
 *
 * @implements Rule<ListNode>
 */
final class ListRule implements Rule
{
    public const PRIORITY = 90;

    /**
     * A regex matching all bullet list markers and a subset of the enumerated list markers.
     *
     * @see https://regex101.com/r/LBXWFV/1
     * @see https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#bullet-lists
     * @see https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#enumerated-lists
     */
    private const LIST_MARKER_REGEX = '/
        ^(
            [-+*\x{2022}\x{2023}\x{2043}]     # match bullet list markers: "*", "+", "-", "•", "‣", or "⁃"        
        )
        (?:\s+|$)
         # capture the spaces between marker and text to determine the list item text offset
         # (or eol, if text starts on a new line)
        /ux';

    public function __construct(
        private readonly RuleContainer $productions,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function applies(BlockContext $blockContext): bool
    {
        $documentIterator = $blockContext->getDocumentIterator();

        return $this->isListLine($documentIterator->current());
    }

    public function apply(BlockContext $blockContext, CompoundNode|null $on = null): Node|null
    {
        $documentIterator = $blockContext->getDocumentIterator();

        $buffer = new Buffer();
        //First line sets the listmarker of the list, and the indentation of the current item.
        $listConfig = $this->getItemConfig($documentIterator->current());
        if (trim($documentIterator->current()) !== $listConfig['marker']) {
            $buffer->push(mb_substr($documentIterator->current(), $listConfig['indenting']));
        }

        $items = [];

        while (
            $this->isListItemStart($documentIterator->getNextLine(), $listConfig['marker'])
                || LinesIterator::isBlockLine($documentIterator->getNextLine(), $listConfig['indenting'])
        ) {
            $documentIterator->next();

            if ($this->isListItemStart($documentIterator->current())) {
                $listConfig = $this->getItemConfig($documentIterator->current());
                $items[] = $this->parseListItem($listConfig, $buffer, $blockContext);
                $buffer = new Buffer();
            }

            // the list item offset is determined by the offset of the first text.
            // An offset of 1 or lower indicates that the list line didn't contain any text.
            if ($listConfig['indenting'] <= 1) {
                $listConfig['indenting'] = strlen($documentIterator->current()) - strlen(
                    ltrim($documentIterator->current()),
                );
            }

            if (trim($documentIterator->current()) === $listConfig['marker']) {
                continue;
            }

            $buffer->push(mb_substr($documentIterator->current(), $listConfig['indenting']));
        }

        $items[] = $this->parseListItem($listConfig, $buffer, $blockContext);

        return new ListNode($items, false);
    }

    private function isListLine(string|null $line): bool
    {
        if ($line === null) {
            return false;
        }

        return preg_match(self::LIST_MARKER_REGEX, $line) > 0;
    }

    /** @return array{marker: string, indenting: int} */
    public function getItemConfig(string $line): array
    {
        $isList = preg_match(self::LIST_MARKER_REGEX, $line, $m) === 1;
        if (!$isList) {
            throw new InvalidArgumentException('Line is not a valid item line');
        }

        return [
            'marker' => $m[1],
            'indenting' => $m[0] === $line ? 1 : mb_strlen($m[0]),
        ];
    }

    private function isListItemStart(string|null $line, string|null $listMarker = null): bool
    {
        if ($line === null) {
            return false;
        }

        $isList = preg_match(self::LIST_MARKER_REGEX, $line, $m) === 1;
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
    private function parseListItem(array $listConfig, Buffer $buffer, BlockContext $blockContext): ListItemNode
    {
        $listItem = new ListItemNode($listConfig['marker'], false, []);
        $subContext = new BlockContext($blockContext->getDocumentParserContext(), $buffer->getLinesString());
        while ($subContext->getDocumentIterator()->valid()) {
            $this->productions->apply($subContext, $listItem);
        }

        $nodes = $listItem->getChildren();
        if (count($nodes) > 1) {
            return $listItem;
        }

        if (!isset($nodes[0])) {
            return $listItem;
        }

        // the list item offset is determined by the offset of the first text
        if ($nodes[0] instanceof ParagraphNode) {
            return new ListItemNode($listConfig['marker'], false, $nodes[0]->getChildren());
        }

        return $listItem;
    }
}
