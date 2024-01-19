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

namespace phpDocumentor\Guides\Code\Highlighter;

interface Highlighter
{
    /** @param array<string, string|null> $debugInformation */
    public function __invoke(string $language, string $code, array $debugInformation): HighlightResult;
}
