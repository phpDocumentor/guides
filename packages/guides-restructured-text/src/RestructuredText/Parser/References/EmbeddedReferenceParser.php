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

namespace phpDocumentor\Guides\RestructuredText\Parser\References;

use function preg_match;
use function str_replace;
use function trim;

trait EmbeddedReferenceParser
{
    /**
     * https://regex101.com/r/8O8N3h/2
     */
    private string $referenceRegex = '/^(.*?)((?<!\\\\)<([^<]+)(?<!\\\\)>)?$/s';

    private function extractEmbeddedReference(string $text): ReferenceData
    {
        preg_match($this->referenceRegex, $text, $matches);

        $text = $matches[1] === '' ? null : trim($matches[1]);
        $reference = trim($matches[1]);

        if (isset($matches[3])) {
            // there is an embedded URI, text and URI are different
            $reference = $matches[3];
        } else {
            $text = null;
        }

        return new ReferenceData(
            str_replace(['\\<', '\\>'], ['<', '>'], $reference),
            $text,
        );
    }
}
