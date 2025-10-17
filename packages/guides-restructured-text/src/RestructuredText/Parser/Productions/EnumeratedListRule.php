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

use function count;
use function ltrim;
use function mb_strlen;
use function mb_substr;
use function ord;
use function preg_match;
use function sprintf;
use function str_ends_with;
use function str_starts_with;
use function strlen;
use function strpos;
use function strtolower;
use function strtoupper;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#enumerated-lists
 *
 * @implements Rule<ListNode>
 */
final class EnumeratedListRule implements Rule
{
    public const PRIORITY = 80;

    private const ROMAN_NUMBER = '((?:M{0,3})(?:CM|CD|D?C{0,3})(?:XC|XL|L?X{0,3})(?:IX|IV|V?I{0,3}))(?<!^)';
    private const NUMBER = '(\d+|#)';

    private const ALPHABETIC = '([a-z])';

    private const LIST_MARKER = '(^%s([\.)])(?:\s+|$)|^[(]%s[)](?:\s+|$))';

    private readonly string $expression;

    public function __construct(private readonly RuleContainer $productions)
    {
        $expression = sprintf(self::LIST_MARKER, self::NUMBER, self::NUMBER);
        $expression .= '|' . sprintf(self::LIST_MARKER, self::ROMAN_NUMBER, self::ROMAN_NUMBER);
        $expression .= '|' . sprintf(self::LIST_MARKER, self::ALPHABETIC, self::ALPHABETIC);
        $this->expression = '/' . $expression . '/i';
    }

    public function applies(BlockContext $blockContext): bool
    {
        $documentIterator = $blockContext->getDocumentIterator();
        if ($this->isListLine($documentIterator->current()) === false) {
            return false;
        }

        $listConfig = $this->getItemConfig($documentIterator->current());

        return LinesIterator::isNullOrEmptyLine($documentIterator->getNextLine()) ||
            LinesIterator::isBlockLine($documentIterator->getNextLine()) ||
            $this->isListItemStart($documentIterator->getNextLine(), $listConfig['marker_type']);
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
            $this->isListItemStart($documentIterator->getNextLine(), $listConfig['marker_type'])
                || LinesIterator::isBlockLine($documentIterator->getNextLine(), $listConfig['indenting'])
        ) {
            $documentIterator->next();

            if ($this->isListItemStart($documentIterator->current())) {
                $items[] = $this->parseListItem($listConfig, $buffer, $blockContext);
                $listConfig = $this->getItemConfig($documentIterator->current());
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
        $start = null;
        $orderType = null;
        if (isset($items[0])) {
            $orderType = $items[0]->getOrderType();
            $start = (string) $this->getStartValue($items[0]->getOrderNumber(), $orderType);
        }

        return new ListNode($items, true, $start, $orderType);
    }

    private function isListLine(string|null $line): bool
    {
        if ($line === null) {
            return false;
        }

        return preg_match($this->expression, $line) > 0;
    }

    /** @return array{marker: string, indenting: int, marker_type: string} */
    private function getItemConfig(string $line): array
    {
        $isList = preg_match($this->expression, $line, $m) > 0;
        if (!$isList) {
            throw new InvalidArgumentException('Line is not a valid item line');
        }

        return [
            'marker' => trim($m[0]),
            'marker_type' => $this->getMarkerType($m[0]),
            'indenting' => $m[0] === $line ? 1 : mb_strlen($m[0]),
        ];
    }

    private function isListItemStart(string|null $line, string|null $listMarker = null): bool
    {
        if (LinesIterator::isNullOrEmptyLine($line)) {
            return false;
        }

        $isList = preg_match($this->expression, $line, $m) > 0;
        if (!$isList) {
            return false;
        }

        $normalizedMarker = $this->getMarkerType($m[0]);

        if ($normalizedMarker === 'unknown') {
            return false;
        }

        if ($listMarker !== null) {
            return $normalizedMarker === $listMarker;
        }

        return true;
    }

    /** @param array{marker: string, indenting: int, marker_type: string} $listConfig */
    private function parseListItem(array $listConfig, Buffer $buffer, BlockContext $blockContext): ListItemNode
    {
        $marker = trim($listConfig['marker'], '.()');
        $orderNumber = null;
        if ($marker !== '#') {
            $orderNumber = $marker;
        }

        $listItem = new ListItemNode($marker, false, [], $orderNumber);
        $subContext = new BlockContext($blockContext->getDocumentParserContext(), $buffer->getLinesString(), false, $blockContext->getDocumentIterator()->key());
        while ($subContext->getDocumentIterator()->valid()) {
            $this->productions->apply($subContext, $listItem);
        }

        $nodes = $listItem->getChildren();
        if (count($nodes) > 1) {
            return $listItem;
        }

        // the list item offset is determined by the offset of the first text
        if ($nodes[0] instanceof ParagraphNode) {
            return new ListItemNode($marker, false, $nodes[0]->getChildren(), $orderNumber, $listConfig['marker_type']);
        }

        return $listItem;
    }

    private function getMarkerType(string $marker): string
    {
        $marker = trim($marker);

        if (LinesIterator::isEmptyLine($marker)) {
            return 'unknown';
        }

        if (preg_match('/' . sprintf(self::LIST_MARKER, self::NUMBER, self::NUMBER) . '/', $marker)) {
            if (strpos($marker, '#')) {
                return 'auto_number' . $this->markerSuffix($marker);
            }

            return 'number' . $this->markerSuffix($marker);
        }

        if (preg_match('/' . sprintf(self::LIST_MARKER, self::ROMAN_NUMBER, self::ROMAN_NUMBER) . '/', $marker)) {
            return 'roman' . $this->markerSuffix($marker);
        }

        if (preg_match('/' . sprintf(self::LIST_MARKER, self::ALPHABETIC, self::ALPHABETIC) . '/', $marker)) {
            return 'alphabetic' . $this->markerSuffix($marker);
        }

        return 'unknown';
    }

    private function markerSuffix(string $marker): string
    {
        if (str_ends_with($marker, '.')) {
            return 'dot';
        }

        if (str_starts_with($marker, '(')) {
            return 'parentheses';
        }

        if (str_ends_with($marker, ')')) {
            return 'right-parenthesis';
        }

        return '';
    }

    private function getStartValue(string|null $firstItemNumber, string|null $orderType): int|null
    {
        if ($firstItemNumber === null) {
            return null;
        }

        if ($orderType === 'auto_number' . $this->markerSuffix($firstItemNumber)) {
            return null;
        }

        if (preg_match('/^\d+$/', $firstItemNumber)) {
            return (int) $firstItemNumber;
        }

        if (preg_match('/^' . self::ROMAN_NUMBER . '$/i', $firstItemNumber, $m)) {
            $roman = strtoupper($m[1]);
            $map = [
                'M' => 1000,
                'CM' => 900,
                'D' => 500,
                'CD' => 400,
                'C' => 100,
                'XC' => 90,
                'L' => 50,
                'XL' => 40,
                'X' => 10,
                'IX' => 9,
                'V' => 5,
                'IV' => 4,
                'I' => 1,
            ];

            $number = 0;
            $i = 0;
            while ($i < strlen($roman)) {
                foreach ($map as $symbol => $value) {
                    if (str_starts_with(mb_substr($roman, $i), $symbol)) {
                        $number += $value;
                        $i += strlen($symbol);
                        break;
                    }
                }
            }

            return $number;
        }

        if (preg_match('/^[a-z]$/i', $firstItemNumber)) {
            return ord(strtolower($firstItemNumber)) - ord('a') + 1;
        }

        return 1;
    }
}
