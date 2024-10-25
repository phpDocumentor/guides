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

namespace phpDocumentor\Guides\RestructuredText\Parser\Interlink;

use function preg_match;

final class DefaultInterlinkParser implements InterlinkParser
{
    /** @see https://regex101.com/r/htMn5p/1 */
    private const INTERLINK_REGEX = '/^([a-zA-Z0-9-_]+):(.*$)/';

    public function extractInterlink(string $fullReference): InterlinkData
    {
        if (!preg_match(self::INTERLINK_REGEX, $fullReference, $matches)) {
            return new InterlinkData($fullReference, '');
        }

        return new InterlinkData($matches[2], $matches[1]);
    }
}
