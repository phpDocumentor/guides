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

namespace phpDocumentor\Guides\RstTheme\Configuration;

enum HeaderSyntax: int
{
    case H1 = 1;
    case H2 = 2;
    case H3 = 3;
    case H4 = 4;
    case H5 = 5;
    case H6 = 6;

    public function delimiter(): string
    {
        return match ($this) {
            HeaderSyntax::H1, HeaderSyntax::H2 => '=',
            HeaderSyntax::H3 => '-',
            HeaderSyntax::H4 => '~',
            HeaderSyntax::H5 => '#',
            HeaderSyntax::H6 => '*',
        };
    }

    public function hasTopDelimiter(): bool
    {
        return match ($this) {
            HeaderSyntax::H1 => true,
            default => false,
        };
    }
}
