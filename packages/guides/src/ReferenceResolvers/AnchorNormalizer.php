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

namespace phpDocumentor\Guides\ReferenceResolvers;

/**
 * Normalizes raw data used to generate anchors.
 *
 * Can be used to ignore case and special signs.
 */
interface AnchorNormalizer
{
    public function reduceAnchor(string $rawAnchor): string;
}
