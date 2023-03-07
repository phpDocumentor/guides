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

use phpDocumentor\Guides\Nodes\CompoundNode;
use InvalidArgumentException;
use phpDocumentor\Guides\Nodes\ListItemNode;
use phpDocumentor\Guides\Nodes\ListNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\RestructuredText\Parser\Buffer;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;

use function count;
use function ltrim;
use function mb_strlen;
use function preg_match;
use function strlen;
use function strpos;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#enumerated-lists
 * @implements Rule<ListNode>
 */
final class EnumeratedListRule implements Rule
{
    private const ROMAN_NUMBER = '((?:M{0,3})(?:CM|CD|D?C{0,3})(?:XC|XL|L?X{0,3})(?:IX|IV|V?I{0,3}))(?<!^)';
    private const NUMBER = '(\d+|#)';

    private const ALPHABETIC = '[a-z]';

    private const LIST_MARKER = '(^%s([\.)])(?:\s+|$)|^[(]%s[)](?:\s+|$))';

    private string $expression;

    private RuleContainer $productions;

    public function __construct(RuleContainer $productions)
    {
        $this->productions = $productions;
        $expression = sprintf(self::LIST_MARKER, self::NUMBER, self::NUMBER);
        $expression .= '|' . sprintf(self::LIST_MARKER, self::ROMAN_NUMBER, self::ROMAN_NUMBER);
        $expression .= '|' . sprintf(self::LIST_MARKER, self::ALPHABETIC, self::ALPHABETIC);
        $this->expression = '/' . $expression . '/i';
    }

    public function applies(DocumentParserContext $documentParser): bool
    {
        $documentIterator = $documentParser->getDocumentIterator();
        if ($this->isListLine($documentIterator->current()) === false) {
            return false;
        }

        $listConfig = $this->getItemConfig($documentIterator->current());


        return LinesIterator::isNullOrEmptyLine($documentIterator->getNextLine()) ||
            $documentIterator->isBlockLine($documentIterator->getNextLine()) ||
            $this->isListItemStart($documentIterator->getNextLine(), $listConfig['marker_type']);
    }

    public function apply(DocumentParserContext $documentParserContext, ?CompoundNode $on = null): ?Node
    {
        $documentIterator = $documentParserContext->getDocumentIterator();

        $buffer = new Buffer();
        //First line sets the listmarker of the list, and the indentation of the current item.
        $listConfig = $this->getItemConfig($documentIterator->current());
        if (trim($documentIterator->current()) !== $listConfig['marker']) {
            $buffer->push(mb_substr($documentIterator->current(), $listConfig['indenting']));
        }

        $items = [];

        while ($this->isListItemStart($documentIterator->getNextLine(), $listConfig['marker_type'])) {


            do {
                $documentIterator->next();

                if ($this->isListItemStart($documentIterator->current())) {
                    $items[] = $this->parseListItem($listConfig, $buffer, $documentParserContext);
                    $listConfig = $this->getItemConfig($documentIterator->current());
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
            } while ($documentIterator->isBlockLine($documentIterator->getNextLine()));
        }



        $items[] = $this->parseListItem($listConfig, $buffer, $documentParserContext);

        return new ListNode($items, true);
    }

    private function isListLine(?string $line): bool
    {
        if ($line === null) {
            return false;
        }

        $isList = preg_match($this->expression, $line) > 0;
        if (!$isList) {
            return false;
        }

        return true;
    }

    /** @return array{marker: string, indenting: int, marker_type: string} */
    private function getItemConfig(string $line): array
    {
        $isList = preg_match($this->expression, $line, $m) > 0;
        if (!$isList) {
            throw new InvalidArgumentException('Line is not a valid item line');
        }

        return [
            'marker' => trim($m[1]),
            'marker_type' => $this->getMarkerType($m[1]),
            'indenting' => $m[0] === $line ? 1 : mb_strlen($m[0])
        ];
    }

    private function isListItemStart(?string $line, ?string $listMarker = null): bool
    {
        if (LinesIterator::isNullOrEmptyLine($line)) {
            return false;
        }

        $isList = preg_match($this->expression, $line, $m) > 0;
        if (!$isList) {
            return false;
        }

        $normalizedMarker = $this->getMarkerType($m[1]);

        if ($normalizedMarker === 'unknown') {
            return false;
        }

        if ($listMarker !== null) {
            return $normalizedMarker === $listMarker;
        }

        return true;
    }

    /** @param array{marker: string, indenting: int} $listConfig */
    private function parseListItem(array $listConfig, Buffer $buffer, DocumentParserContext $context): ListItemNode
    {
        $marker = trim($listConfig['marker'], '.()');
        $listItem = new ListItemNode($marker, false, []);
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
            return new ListItemNode($marker, false, $nodes[0]->getChildren());
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

            return 'number'  . $this->markerSuffix($marker);
        }

        if (preg_match('/' . sprintf(self::LIST_MARKER, self::ROMAN_NUMBER, self::ROMAN_NUMBER) . '/', $marker)) {
            return 'roman'  . $this->markerSuffix($marker);
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
}
