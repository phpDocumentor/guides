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

namespace phpDocumentor\Guides\RestructuredText\Parser;

use function filter_var;
use function preg_match;

use const FILTER_VALIDATE_INT;

final class AnnotationUtility
{
    public static function isFootnoteKey(string $key): bool
    {
        return self::isAnonymousFootnote($key)
            || self::getFootnoteName($key) !== null
            || self::getFootnoteNumber($key) !== null;
    }

    public static function isAnonymousFootnote(string $key): bool
    {
        return $key === '#';
    }

    public static function getFootnoteName(string $key): string|null
    {
        preg_match('/^[#][a-zA-Z0-9]*$/msi', $key, $matches);

        return $matches[0] ?? null;
    }

    public static function getFootnoteNumber(string $key): int|null
    {
        $intValue = filter_var($key, FILTER_VALIDATE_INT);
        if ($intValue === false || $intValue < 1) {
            return null;
        }

        return $intValue;
    }
}
