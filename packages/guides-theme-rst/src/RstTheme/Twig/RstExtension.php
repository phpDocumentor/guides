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

use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\RstTheme\Configuration\HeaderSyntax;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

use function array_map;
use function explode;
use function implode;
use function min;
use function preg_replace;
use function rtrim;
use function str_repeat;
use function strlen;

final class RstExtension extends AbstractExtension
{
    /** @return TwigFunction[] */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('renderRstTitle', $this->renderRstTitle(...), ['is_safe' => ['rst'], 'needs_context' => false]),
            new TwigFunction('renderRstIndent', $this->renderRstIndent(...), ['is_safe' => ['rst'], 'needs_context' => false]),
        ];
    }

    /** @return TwigFilter[] */
    public function getFilters(): array
    {
        return [
            new TwigFilter('clean_content', [$this, 'cleanContent']),
        ];
    }

    public function cleanContent(string $content): string
    {
        $lines = explode("\n", $content);
        $lines = array_map('rtrim', $lines);
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

        return $ret;
    }
}
