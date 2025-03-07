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

namespace phpDocumentor\Guides\RstTheme\Twig;

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Inline\InlineNodeInterface;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Table\TableColumn;
use phpDocumentor\Guides\Nodes\Table\TableRow;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\RstTheme\Configuration\HeaderSyntax;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

use function array_map;
use function explode;
use function implode;
use function max;
use function mb_str_pad;
use function mb_strlen;
use function min;
use function preg_replace;
use function rtrim;
use function str_repeat;
use function strlen;

final class RstExtension extends AbstractExtension
{
    public function __construct(
        private NodeRenderer $nodeRenderer,
    ) {
    }

    /** @return TwigFunction[] */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('renderRstTitle', $this->renderRstTitle(...), ['is_safe' => ['rst'], 'needs_context' => false]),
            new TwigFunction('renderRstTable', $this->renderRstTable(...), ['is_safe' => ['rst'], 'needs_context' => true]),
            new TwigFunction('renderRstIndent', $this->renderRstIndent(...), ['is_safe' => ['rst'], 'needs_context' => false]),
        ];
    }

    /** @return TwigFilter[] */
    public function getFilters(): array
    {
        return [
            new TwigFilter('clean_content', $this->cleanContent(...)),
            new TwigFilter('plaintext', $this->plaintext(...)),
        ];
    }

    public function plaintext(InlineNodeInterface $node): string
    {
        if ($node instanceof InlineCompoundNode) {
            return implode('', array_map($this->plaintext(...), $node->getChildren()));
        }

        return $node->toString();
    }

    public function cleanContent(string $content): string
    {
        $lines = explode("\n", $content);
        $lines = array_map(rtrim(...), $lines);
        $content = implode("\n", $lines);

        $content = preg_replace('/(\n){2,}/', "\n\n", $content);

        return rtrim($content) . "\n";
    }

    public function renderRstIndent(string $text, int $indentNr): string
    {
        $indent = str_repeat(' ', $indentNr * 4);

        return preg_replace('/^/m', $indent, $text);
    }

    public function renderRstTitle(TitleNode $node, string $content): string
    {
        $headerSyntax = HeaderSyntax::from(min($node->getLevel(), 6));
        $ret = '';
        if ($headerSyntax->hasTopDelimiter()) {
            $ret .= str_repeat($headerSyntax->delimiter(), strlen($content)) . "\n";
        }

        $ret .= $content . "\n" . str_repeat($headerSyntax->delimiter(), strlen($content));

        return $ret . "\n";
    }

    /** @param array{env: RenderContext} $context */
    public function renderRstTable(array $context, TableNode $node): string
    {
        $columnWidths = [];

        $this->determineMaxLenght($node->getHeaders(), $context['env'], $columnWidths);
        $this->determineMaxLenght($node->getData(), $context['env'], $columnWidths);

        $ret = $this->renderTableRowEnd($columnWidths);
        $ret .= $this->renderRows($node->getHeaders(), $context['env'], $columnWidths, '=');
        $ret .= $this->renderRows($node->getData(), $context['env'], $columnWidths);

        return $ret . "\n";
    }

    private function renderCellContent(RenderContext $env, TableColumn $column): string
    {
        return implode('', array_map(fn ($node) => $this->nodeRenderer->render($node, $env), $column->getValue()));
    }

    /**
     * @param TableRow[] $rows
     * @param int[] &$columnWidths
     */
    private function determineMaxLenght(array $rows, RenderContext $env, array &$columnWidths): void
    {
        foreach ($rows as $row) {
            foreach ($row->getColumns() as $index => $column) {
                $content = $this->renderCellContent($env, $column);

                $columnWidths[$index] = max(mb_strlen($content) + 2, $columnWidths[$index] ?? 0);
            }
        }
    }

    /**
     * @param TableRow[] $rows
     * @param int[] $columnWidths
     */
    private function renderRows(array $rows, RenderContext $env, array $columnWidths, string $separator = '-'): string
    {
        $ret = '';
        foreach ($rows as $row) {
            $ret .= '|';
            foreach ($row->getColumns() as $index => $column) {
                $content = $this->renderCellContent($env, $column);

                $ret .= ' ' . mb_str_pad($content, $columnWidths[$index] - 2) . ' |';
            }

            $ret .= "\n" . $this->renderTableRowEnd($columnWidths, $separator);
        }

        return $ret;
    }

    /** @param int[] $columnWidths */
    private function renderTableRowEnd(array $columnWidths, string $char = '-'): string
    {
        $ret = '';
        foreach ($columnWidths as $width) {
            $ret .= '+' . str_repeat($char, $width);
        }

        $ret .= '+' . "\n";

        return $ret;
    }
}
