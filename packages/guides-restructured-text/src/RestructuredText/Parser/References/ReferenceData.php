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

final class ReferenceData
{
    public function __construct(
        public readonly string $reference,
        public readonly string|null $text,
    ) {
    }
}
